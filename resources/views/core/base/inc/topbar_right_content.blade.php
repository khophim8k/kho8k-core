<!-- This file is used to store topbar (right) items -->

<li class="nav-item dropdown"><a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true"
        aria-expanded="false" style="position: relative;width: 35px;height: 35px;margin: 0 10px;"><i class="la la-gears"
            style="font-size: 2.3rem"></i></a>
    <div
        class="dropdown-menu {{ config('backpack.base.html_direction') == 'rtl' ? 'dropdown-menu-left' : 'dropdown-menu-right' }} mr-4 pb-1 pt-1">
        <a class="dropdown-item" href="{{ backpack_url('quick-action/delete-cache') }}">
            Delete All Cache
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ backpack_url('quick-action/updateCMS') }}">
            Update CMS
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ backpack_url('quick-action/relink') }}">
            fix lỗi mất hình
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ backpack_url('quick-action/refile') }}">
            Cài filemanager
        </a>
    </div>
</li>
