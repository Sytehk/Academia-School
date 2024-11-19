@extends('themes.theme-one.layouts.app')

@section('content')
    <section class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 py-14">

        <div class="max-w-4xl mx-auto px-4">

            {{-- HEADER --}}
            <div class="text-center mb-10">
                <h1 class="text-4xl md:text-5xl font-black text-slate-800">
                    Student Admission
                </h1>
                <p class="text-slate-500 mt-2">
                    Fill all required information carefully
                </p>
            </div>

            <div>
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-green-100 text-green-700 border border-green-300">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-red-100 text-red-700 border border-red-300">
                        {{ session('error') }}
                    </div>
                @endif
            </div>

            {{-- FORM CARD --}}
            <form id="admissionForm" action="{{ route('admission-form-submit') }}" method="POST" enctype="multipart/form-data"
                class="bg-white/70 backdrop-blur-xl border border-slate-200 shadow-2xl rounded-[30px] p-6 md:p-10 space-y-10">

                @csrf

                {{-- ================= BASIC INFO ================= --}}
                <div class="space-y-5">

                    <h2 class="text-xl font-bold text-slate-700 border-b pb-2">
                        Basic Information
                    </h2>

                    <div class="grid md:grid-cols-2 gap-6">

                        {{-- FIRST NAME --}}
                        <div class="relative">
                            <input type="text" name="first_name" required
                                class="peer w-full border rounded-xl px-4 pt-5 pb-2 outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder=" " />
                            <label class="absolute left-4 top-2 text-sm text-slate-500 peer-focus:text-purple-600">
                                First Name *
                            </label>
                        </div>

                        {{-- LAST NAME --}}
                        <div class="relative">
                            <input type="text" name="last_name"
                                class="peer w-full border rounded-xl px-4 pt-5 pb-2 outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder=" " />
                            <label class="absolute left-4 top-2 text-sm text-slate-500">
                                Last Name
                            </label>
                        </div>

                        {{-- EMAIL --}}
                        <div class="relative">
                            <input type="email" name="email"
                                class="peer w-full border rounded-xl px-4 pt-5 pb-2 outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder=" " />
                            <label class="absolute left-4 top-2 text-sm text-slate-500">
                                Email
                            </label>
                        </div>

                        {{-- PHONE --}}
                        <div class="relative">
                            <input type="text" name="phone" required
                                class="peer w-full border rounded-xl px-4 pt-5 pb-2 outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder=" " />
                            <label class="absolute left-4 top-2 text-sm text-slate-500">
                                Phone *
                            </label>
                        </div>

                        {{-- BIRTHDAY --}}
                        <div>
                            <label class="text-sm text-slate-600">Birthday</label>
                            <input type="date" name="birthday"
                                class="w-full border rounded-xl px-4 py-3 mt-1 focus:ring-2 focus:ring-purple-500">
                        </div>

                        {{-- GENDER --}}
                        <div>
                            <label class="text-sm text-slate-600">Gender</label>
                            <select name="gender"
                                class="w-full border rounded-xl px-4 py-3 mt-1 focus:ring-2 focus:ring-purple-500">
                                <option value="">Select Gender</option>
                                <option>Male</option>
                                <option>Female</option>
                            </select>
                        </div>

                    </div>
                </div>

                {{-- ================= ACADEMIC ================= --}}
                <div class="space-y-5">

                    <h2 class="text-xl font-bold text-slate-700 border-b pb-2">
                        Academic Information
                    </h2>

                    <div class="grid md:grid-cols-3 gap-6">

                        {{-- CLASS --}}
                        <div>
                            <label class="text-sm">Class *</label>
                            <select name="class_id" id="class_id" required
                                class="w-full border rounded-xl px-4 py-3 mt-1 focus:ring-2 focus:ring-purple-500">
                                <option value="">Select Class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">
                                        {{ $class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm">Previous School</label>
                            <input type="text" name="previous_school" class="w-full border rounded-xl px-4 py-3 mt-1">
                        </div>

                        <div>
                            <label class="text-sm">Previous Class</label>
                            <input type="text" name="previous_class" class="w-full border rounded-xl px-4 py-3 mt-1">
                        </div>

                    </div>
                </div>

                {{-- ================= GUARDIAN ================= --}}
                <div class="space-y-5">

                    <h2 class="text-xl font-bold text-slate-700 border-b pb-2">
                        Guardian Information
                    </h2>

                    <div class="grid md:grid-cols-2 gap-6">

                        {{-- NAME --}}
                        <div>
                            <label class="text-sm text-slate-600">Guardian Name</label>
                            <input type="text" name="guardian_name"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 mt-1
                          focus:ring-2 focus:ring-purple-500 focus:border-purple-500
                          outline-none transition">
                        </div>

                        {{-- PHONE --}}
                        <div>
                            <label class="text-sm text-slate-600">Guardian Phone</label>
                            <input type="text" name="guardian_phone"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 mt-1
                          focus:ring-2 focus:ring-purple-500 focus:border-purple-500
                          outline-none transition">
                        </div>

                        {{-- EMAIL --}}
                        <div>
                            <label class="text-sm text-slate-600">Guardian Email</label>
                            <input type="email" name="guardian_email"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 mt-1
                          focus:ring-2 focus:ring-purple-500 focus:border-purple-500
                          outline-none transition">
                        </div>

                        {{-- RELATION --}}
                        <div>
                            <label class="text-sm text-slate-600">Relation</label>
                            <input type="text" name="guardian_relation"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 mt-1
                          focus:ring-2 focus:ring-purple-500 focus:border-purple-500
                          outline-none transition">
                        </div>

                    </div>
                </div>

                {{-- ================= EXTRA ================= --}}
                <div class="space-y-6">

                    <h2 class="text-xl font-bold text-slate-700 border-b pb-2">
                        Address & Extra Information
                    </h2>

                    {{-- ADDRESS --}}
                    <div class="relative">
                        <textarea name="address" rows="3"
                            class="peer w-full border border-slate-200 rounded-2xl px-4 pt-6 pb-3
                         focus:ring-2 focus:ring-purple-500 focus:border-purple-500
                         outline-none transition resize-none"
                            placeholder=" "></textarea>

                        <label
                            class="absolute left-4 top-2 text-sm text-slate-500
                      peer-focus:text-purple-600">
                            Full Address
                        </label>
                    </div>

                    {{-- REMARKS --}}
                    <div class="relative">
                        <textarea name="remarks" rows="3"
                            class="peer w-full border border-slate-200 rounded-2xl px-4 pt-6 pb-3
                         focus:ring-2 focus:ring-purple-500 focus:border-purple-500
                         outline-none transition resize-none"
                            placeholder=" "></textarea>

                        <label class="absolute left-4 top-2 text-sm text-slate-500">
                            Remarks (Optional)
                        </label>
                    </div>

                    {{-- IMAGE UPLOAD (MODERN UI) --}}
                    <div>
                        <label class="text-sm text-slate-600 mb-2 block">
                            Student Photo (Optional)
                        </label>

                        {{-- PREVIEW BOX --}}
                        <div class="flex items-center gap-6">

                            {{-- IMAGE PREVIEW --}}
                            <div
                                class="w-28 h-28 rounded-2xl border bg-slate-100 overflow-hidden flex items-center justify-center">
                                <img id="previewImage" class="w-full h-full object-cover hidden" alt="Preview">

                                <span id="previewPlaceholder" class="text-xs text-slate-400 text-center px-2">
                                    No Image
                                </span>
                            </div>

                            {{-- UPLOAD BOX --}}
                            <div class="flex-1">

                                <div
                                    class="relative border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center
                        hover:border-purple-400 transition bg-slate-50">

                                    <input type="file" name="image" accept="image/*"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        onchange="previewFile(event)">

                                    <div class="space-y-1">
                                        <div class="text-3xl">📤</div>
                                        <p class="text-slate-600 font-medium">
                                            Click or drag to upload
                                        </p>
                                        <p class="text-xs text-slate-400">
                                            PNG, JPG up to 2MB
                                        </p>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>

                {{-- SUBMIT --}}
                <div class="text-center pt-5">

                    <button type="submit"
                        class="bg-gradient-to-r from-purple-600 to-pink-500 text-white px-10 py-4 rounded-full font-bold shadow-xl hover:scale-105 transition">

                        Submit Application

                    </button>

                </div>

            </form>
        </div>

    </section>

    <script>
        document.getElementById("admissionForm").addEventListener("submit", function(e) {

            let required = ["first_name", "phone", "class_id"];
            let valid = true;

            required.forEach(name => {
                let el = document.querySelector(`[name="${name}"]`);

                if (!el.value.trim()) {
                    el.classList.add("border-red-500", "ring-2", "ring-red-200");
                    valid = false;
                } else {
                    el.classList.remove("border-red-500");
                }
            });

            if (!valid) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            }
        });
    </script>

    <script>
        function previewFile(event) {
            const input = event.target;
            const file = input.files[0];

            const preview = document.getElementById('previewImage');
            const placeholder = document.getElementById('previewPlaceholder');

            if (file) {

                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.style.display = "none";
                }

                reader.readAsDataURL(file);

            } else {
                preview.classList.add('hidden');
                placeholder.style.display = "block";
            }
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const success = document.querySelector('.bg-green-100');

            if (success) {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    </script>
@endsection
