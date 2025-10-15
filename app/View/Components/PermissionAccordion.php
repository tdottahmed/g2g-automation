<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PermissionAccordion extends Component
{
    public $permissionGroups;

    /**
     * Create a new component instance.
     *
     * @param array $permissionGroups
     */
    public function __construct($permissionGroups)
    {
        $this->permissionGroups = $permissionGroups;
    }

    /**
     * Get the view / contents that represent the component.
     */

    public function render(): View|Closure|string
    {
        return view('components.permission-accordion');
    }
}
