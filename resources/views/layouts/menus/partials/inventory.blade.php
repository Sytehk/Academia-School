<div data-kt-menu-trigger="click"
    class="menu-item menu-accordion
{{ Request::is('inventory-categories*') ||
Request::is('inventory-items*') ||
Request::is('inventory-sales*')
    ? 'here show'
    : '' }}">

    <span class="menu-link">
        <span class="menu-icon">
            <i class="fa-solid fa-boxes-stacked fs-2"></i>
        </span>
        <span class="menu-title">{{ _lang('Inventory') }}</span>
        <span class="menu-arrow"></span>
    </span>

    <div class="menu-sub menu-sub-accordion">
        <div class="menu-item">
            <a class="menu-link {{ Request::is('inventory-categories*') ? 'active' : '' }}"
                href="{{ route('inventory-categories.index') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">{{ _lang('Categories') }}</span>
            </a>
        </div>
        <div class="menu-item">
            <a class="menu-link {{ Request::is('inventory-items*') ? 'active' : '' }}"
                href="{{ route('inventory-items.index') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">{{ _lang('Items') }}</span>
            </a>
        </div>
        <div class="menu-item">
            <a class="menu-link {{ Request::is('inventory-sales*') ? 'active' : '' }}"
                href="{{ route('inventory-sales.index') }}">
                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                <span class="menu-title">{{ _lang('Sales') }}</span>
            </a>
        </div>
    </div>
</div>
