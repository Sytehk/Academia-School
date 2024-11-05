{{-- Empty state shown when the logged-in parent has no children linked. --}}
<div class="card">
    <div class="card-body text-center py-15">
        <i class="fa-solid fa-children fs-3x text-muted mb-5 d-block"></i>
        <h3 class="text-gray-800">{{ _lang('No child linked to your account') }}</h3>
        <p class="text-muted">
            {{ _lang('Please contact your institute to link your children to your parent account.') }}
        </p>
    </div>
</div>
