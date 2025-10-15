<?php

namespace App\View\Components\Layouts\Admin\Partials;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SidebarMenuItem extends Component
{
    public $route;
    public $icon;
    public $label;
    public $dropdownRoutes;

    public function __construct($route, $icon, $label, $dropdownRoutes = [])
    {
        $this->route = $route;
        $this->icon = $icon;
        $this->label = $label;
        $this->dropdownRoutes = $dropdownRoutes;
    }

    /**
     * Check if the current route is active (either the main route or any dropdown route).
     *
     * @return bool
     */
    public function isActive()
    {
        // Check if the current route matches the main route or any of the dropdown routes
        return $this->isRouteActive($this->route) || $this->isDropdownRouteActive();
    }

    /**
     * Check if the given route or its sub-routes (using wildcard matching) is active.
     *
     * @param  string  $route
     * @return bool
     */
    public function isRouteActive($route)
    {
        // dd($this->dropdownRoutes);

        // Check for wildcard matches (e.g., 'roles.*')
        return request()->routeIs("{$route}.*");
    }

    /**
     * Check if any of the dropdown routes are active.
     *
     * @return bool
     */
    public function isDropdownRouteActive()
    {
        foreach ($this->dropdownRoutes as $dropdownRoute => $subLabel) {
            if ($this->isRouteActive($dropdownRoute)) {
                return true;
            }
        }
        return false;
    }

    public function render(): View|Closure|string
    {
        return view('components.layouts.admin.partials.sidebar-menu-item');
    }
}
