<?php

namespace pxlrbt\FilamentActivityLog\Pages;

use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Enums\PaginationMode;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Livewire\WithPagination;
use pxlrbt\FilamentActivityLog\Pages\Concerns\CanPaginate;

abstract class ListActivitiesByCauser extends Page implements HasForms
{
    use CanPaginate;
    use InteractsWithFormActions;
    use InteractsWithRecord;
    use WithPagination;

    protected string $view = 'filament-activity-log::pages.list-activities-by-causer';

    /** @var array<class-string, Collection<string, string>> */
    protected static array $fieldLabelMaps = [];

    public function mount($record)
    {
        $this->record = $this->resolveRecord($record);
        $this->recordsPerPage = $this->getDefaultRecordsPerPageSelectOption();
    }

    public function getBreadcrumb(): string
    {
        return static::$breadcrumb ?? __('filament-activity-log::activities.breadcrumb');
    }

    public function getTitle(): string
    {
        return __('filament-activity-log::activities.title', ['record' => $this->getRecordTitle()]);
    }

    public function getActivities()
    {
        return $this->paginateQuery(
            $this->record->activitiesAsCauser()->with('subject')->latest()->getQuery()
        );
    }

    public function getPaginationMode(): PaginationMode
    {
        return PaginationMode::Default;
    }

    public function getSubjectResource(string $subjectType): ?string
    {
        $modelClass = Relation::getMorphedModel($subjectType) ?? $subjectType;

        return Filament::getModelResource($modelClass);
    }

    public function getFieldLabel(?string $resourceClass, string $name): string
    {
        if ($resourceClass === null) {
            return $name;
        }

        static::$fieldLabelMaps[$resourceClass] ??= $this->createFieldLabelMap($resourceClass);

        return static::$fieldLabelMaps[$resourceClass][$name] ?? $name;
    }

    /** @param  class-string  $resourceClass */
    protected function createFieldLabelMap(string $resourceClass): Collection
    {
        $schema = $resourceClass::form(new Schema($this));

        $components = collect($schema->getComponents());
        $extracted = collect();

        while (($component = $components->shift()) !== null) {
            if ($component instanceof Field || $component instanceof MorphToSelect) {
                $extracted->push($component);

                continue;
            }

            $children = $component->getChildComponents();

            if (count($children) > 0) {
                $components = $components->merge($children);

                continue;
            }

            $extracted->push($component);
        }

        return $extracted
            ->filter(fn ($field) => $field instanceof Field)
            ->mapWithKeys(fn (Field $field) => [
                $field->getName() => $field->getLabel(),
            ]);
    }
}
