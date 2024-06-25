<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class ClassRoutine extends Model
{
    use BelongsToTenant;

    protected $table = 'class_routines';

    protected $guarded = ['id'];

    public static function RoutineList($class_id = null)
    {
        $query = DB::table('classes')
            ->join('sections', 'classes.id', '=', 'sections.class_id')
            ->leftJoin('class_routines', 'sections.id', '=', 'class_routines.section_id')
            ->select('classes.class_name', 'sections.section_name', 'classes.id as c_id', 'sections.id as s_id', DB::raw('GROUP_CONCAT(class_routines.id) as r_ids'))
            ->where('classes.institute_id', get_institute_id())
            ->where('classes.branch_id', get_branch_id())
            ->when($class_id, function ($query) use ($class_id) {
                return $query->where('classes.id', $class_id);
            })
            ->groupBy('sections.id')
            ->get();

        return $query;
    }

    /**
     * Build the day x subject grid the "Manage routine" page renders, pre-populated with
     * any existing saved routine for each cell.
     *
     * This used to inner-join `class_days` to `subjects` on the meaningless condition
     * `class_days.is_active = 1` (not a real join key) to enumerate active days — but
     * nothing in the app ever inserted rows into `class_days` (no seeder, no admin UI), so
     * that join always returned zero rows. The grid was built entirely from that join, so
     * the Manage page showed "No routines available" for every institute regardless of
     * whether class_routines actually had saved data — the save/update flow was never
     * broken, the page just never rendered anything to populate.
     */
    public static function getRoutine($class = null, $section = null)
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $activeDays = ClassDay::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->where('is_active', 1)
            ->orderBy('id')
            ->pluck('day');

        // First time this institute/branch has ever opened the Manage page: no working-days
        // configuration exists yet. Self-provision the standard 6-day school week (matching
        // Modules/Academic/database/seeders/ClassRoutineSeeder.php's day list) instead of
        // rendering an empty grid forever. Safe to re-run — firstOrCreate is idempotent.
        if ($activeDays->isEmpty()) {
            $defaultDays = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
            foreach ($defaultDays as $day) {
                ClassDay::firstOrCreate(
                    ['institute_id' => $instituteId, 'branch_id' => $branchId, 'day' => $day],
                    ['is_active' => 1]
                );
            }
            $activeDays = collect($defaultDays);
        }

        $subjects = Subject::where('class_id', $class)->orderBy('id')->get(['id', 'subject_name']);

        $routines = [];

        foreach ($activeDays as $day) {
            foreach ($subjects as $subject) {
                $routine = static::where('institute_id', $instituteId)
                    ->where('branch_id', $branchId)
                    ->where('class_id', $class)
                    ->where('section_id', $section)
                    ->where('subject_id', $subject->id)
                    ->where('day', $day)
                    ->first();

                $routines[$day][$subject->id] = [
                    'subject' => (object) ['s_id' => $subject->id, 'subject_name' => $subject->subject_name],
                    'routine' => $routine,
                ];
            }
        }

        return $routines;
    }

    public static function getRoutineView(int $classId, int $sectionId)
    {
        $subjects = Subject::where('class_id', $classId)
            ->with([
                'routines' => function ($query) use ($sectionId) {
                    $query->where('section_id', $sectionId)
                        ->with('teacher');
                },
            ])
            ->orderBy('id')
            ->get();

        $routines = [];
        foreach ($subjects as $subject) {
            foreach ($subject->routines as $routine) {
                $day = $routine->day;
                if (! isset($routines[$day])) {
                    $routines[$day] = [];
                }
                // Object, not array: routine_view.blade.php reads these via -> on both its
                // "classic" and "modern" tabs.
                $routines[$day][] = (object) [
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->subject_name,
                    'teacher_id' => $routine->teacher_id,
                    'teacher_name' => optional($routine->teacher)->name,
                    'start_time' => $routine->start_time,
                    'end_time' => $routine->end_time,
                    'room' => $routine->room,
                ];
            }
        }

        return $routines;
    }

    public static function getTeacherRoutine($teacher_id = '')
    {
        $teacher_id = sql_escape($teacher_id);

        $teacherRoutine = DB::table('class_routines')
            ->select('subjects.subject_name', 'teachers.name as teacher_name', 'class_routines.*', 'sections.id', 'sections.section_name', 'classes.id', 'classes.class_name')
            ->join('subjects', 'class_routines.subject_id', '=', 'subjects.id')
            ->join('teachers', 'class_routines.teacher_id', '=', 'teachers.id')
            ->leftJoin('sections', 'class_routines.section_id', '=', 'sections.id')
            ->leftJoin('classes', 'sections.class_id', '=', 'classes.id')
            ->where('class_routines.teacher_id', $teacher_id)
            ->orderBy('class_routines.start_time')
            ->get();

        // Group the routines by day
        $groupedRoutine = [];
        foreach ($teacherRoutine as $routine) {
            $day = $routine->day;
            if (! isset($groupedRoutine[$day])) {
                $groupedRoutine[$day] = [];
            }
            $groupedRoutine[$day][] = $routine;
        }

        return $groupedRoutine;
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class, 'id', 'teacher_id');
    }

    /**
     * Find an existing routine entry that overlaps the given day/time window for the same
     * teacher or the same room, within the current institute/branch. Two ranges overlap when
     * start1 < end2 AND start2 < end1 — a plain (day, start_time, end_time, teacher_id) unique
     * key only catches identical time ranges, not partial overlaps, so this is checked
     * explicitly at save time rather than relying on a DB constraint.
     */
    public static function findConflict(
        string $day,
        string $startTime,
        string $endTime,
        ?int $teacherId,
        ?string $room,
        ?int $excludeId = null
    ): ?self {
        if (empty($startTime) || empty($endTime) || (empty($teacherId) && empty($room))) {
            return null;
        }

        $query = static::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('day', $day)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($teacherId, $room) {
                if (! empty($teacherId)) {
                    $q->orWhere('teacher_id', $teacherId);
                }
                if (! empty($room)) {
                    $q->orWhere('room', $room);
                }
            });

        return $query->first();
    }
}
