<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class TableViewAction extends Action
{
    /**
     * @return class-string<\Illuminate\Database\Eloquent\Model> | null
     */
    protected ?string $viewTypeModel = null;

    /**
     * @var list<Closure(\Filament\Forms\Components\Field): \Filament\Forms\Components\Field|array<string, Closure(\Filament\Forms\Components\Field): \Filament\Forms\Components\Field>> | null
     */
    protected ?array $modifyFormFieldUsing = null;

    /**
     * @return list<Field>
     */
    protected static function getTableViewActionForm(TableViewAction $action): array
    {
        $components = $action->getDefaultFormFields();
        $modifyFormFieldUsing = $action->getModifyFormFieldUsing();

        if ($modifyFormFieldUsing === null) {
            return $components;
        }

        $components = collect($components);

        foreach ($modifyFormFieldUsing as $modifyFormFieldUsingStatement) {
            if ($modifyFormFieldUsingStatement instanceof Closure) {
                $components->transform($modifyFormFieldUsingStatement);

                continue;
            }

            if (! is_array($modifyFormFieldUsingStatement)) {
                continue;
            }

            foreach ($modifyFormFieldUsingStatement as $name => $modifyFormFieldUsing) {
                $components->transform(static function (Field $field) use ($name, $modifyFormFieldUsing) {
                    return $name === $field->getName() ? $modifyFormFieldUsing($field) : $field;
                });
            }
        }

        return $components->toArray();
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelFQCN
     * @return $this
     */
    public function viewTypeModel(string $modelFQCN): static
    {
        $this->viewTypeModel = $modelFQCN;

        return $this;
    }

    /**
     * @param  Closure(\Filament\Forms\Components\Field): \Filament\Forms\Components\Field|array<string, Closure(\Filament\Forms\Components\Field): \Filament\Forms\Components\Field>  $callback
     * @return $this
     */
    public function modifyFormFieldUsing(Closure|array $callback): static
    {
        $this->modifyFormFieldUsing[] = $callback;

        return $this;
    }

    /**
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    public function getViewTypeModel(): string
    {
        return $this->viewTypeModel;
    }

    /**
     * @return array<Closure(\Filament\Forms\Components\Field): \Filament\Forms\Components\Field|array<string, Closure(\Filament\Forms\Components\Field): \Filament\Forms\Components\Field>> | null
     */
    public function getModifyFormFieldUsing(): ?array
    {
        return $this->modifyFormFieldUsing;
    }

    /**
     * @return list<\Filament\Forms\Components\Field>
     */
    public function getDefaultFormFields(): array
    {
        return [
            TextInput::make('name')
                ->label(__('filament-table-views::toolbar.actions.table-view-action.form.name'))
                ->maxLength(64)
                ->required(),
            // Use `modifyFormFieldUsing` to replace with the appropriate form field.
            Hidden::make('icon')
                ->label(__('filament-table-views::toolbar.actions.table-view-action.form.icon')),
            ColorPicker::make('color')
                ->label(__('filament-table-views::toolbar.actions.table-view-action.form.color')),
            Toggle::make('is_public')
                ->label(__('filament-table-views::toolbar.actions.table-view-action.form.is_public')),
            Toggle::make('is_favorite')
                ->label(__('filament-table-views::toolbar.actions.table-view-action.form.is_favorite')),
            Toggle::make('is_globally_highlighted')
                ->label(__('filament-table-views::toolbar.actions.table-view-action.form.is_globally_highlighted')),
            Textarea::make('description')
                ->label(__('filament-table-views::toolbar.actions.table-view-action.form.description'))
                ->maxLength(65535),
        ];
    }
}
