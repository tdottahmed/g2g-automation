<?php

namespace App\View\Components\DataDisplay;

use Closure;
use Illuminate\View\Component;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;

class DataTable extends Component
{
    public Collection $rows;
    public array $extraActions;
    public bool $showCreatedAt;
    public bool $showUpdatedAt;
    public array $columnsToIgnore;
    public array $ignoreActions = [];

    /**
     * Create a new component instance.
     *
     * @param  mixed $rows
     * @param  array $extraActions
     * @param  bool $createAt
     * @param  bool $updatedAt
     * @param  array $columnsToIgnore
     * @param  array $ignoreActions  
     * @return void
     */
    public function __construct($rows, array $extraActions = [], bool $createAt = false, bool $updatedAt = false, array $columnsToIgnore = [], array $ignoreActions = [])
    {
        $this->rows = collect($rows);
        $this->extraActions = $extraActions;
        $this->showCreatedAt = $createAt;
        $this->showUpdatedAt = $updatedAt;
        $this->columnsToIgnore = $columnsToIgnore;
        $this->ignoreActions = $ignoreActions;
    }

    /**
     * Get default and extra actions for a row.
     *
     * @param  Model $row
     * @return array
     */
    public function getActions(Model $row): array
    {
        $defaultActions = [];

        if (Route::has($row->getTable() . '.edit')) {
            $defaultActions['edit'] = [
                'title' => 'Edit',
                'method' => 'GET',
                'icon' => 'ri-pencil-fill',
                'route' => route($row->getTable() . '.edit', $row->id),
            ];
        }
        if (Route::has($row->getTable() . '.destroy')) {
            $defaultActions['delete'] = [
                'title' => 'Delete',
                'method' => 'DELETE',
                'icon' => 'ri-delete-bin-fill',
                'route' => route($row->getTable() . '.destroy', $row->id),
            ];
        }
        if (Route::has($row->getTable() . '.show')) {
            $defaultActions['show'] = [
                'title' => 'Show',
                'method' => 'GET',
                'icon' => 'ri-eye-fill',
                'route' => route($row->getTable() . '.show', $row->id),
            ];
        }
        foreach ($this->ignoreActions as $ignoreAction) {
            unset($defaultActions[$ignoreAction]);
        }

        $extraActions = collect($this->extraActions)->map(function ($action) use ($row) {
            return [
                'title' => $action['title'],
                'method' => $action['method'],
                'icon' => $action['icon'],
                'route' => route($action['route'], $row->id),
            ];
        })->toArray();
        return array_merge($defaultActions, $extraActions);
    }


    /**
     * Filter visible columns for the table.
     *
     * @return array
     */
    public function getVisibleColumns(): array
    {
        if ($this->rows->isEmpty()) {
            return [];
        }
        $attributes = $this->rows->first()->getAttributes();
        return array_filter(array_keys($attributes), function ($column) {
            return !in_array($column, $this->columnsToIgnore) &&
                (!in_array($column, ['id', 'created_at', 'updated_at']) ||
                    ($this->showCreatedAt && $column === 'created_at') ||
                    ($this->showUpdatedAt && $column === 'updated_at'));
        });
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render(): View|Closure|string
    {
        return view('components.data-display.data-table', [
            'columns' => $this->getVisibleColumns(),
        ]);
    }
}
