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

    protected ?Closure $modifyNameFormComponentUsing = null;

    protected ?Closure $modifyIconFormComponentUsing = null;

    protected ?Closure $modifyColorFormComponentUsing = null;

    protected ?Closure $modifyIsPublicFormComponentUsing = null;

    protected ?Closure $modifyIsFavoriteFormComponentUsing = null;

    protected ?Closure $modifyIsGloballyHighlightedFormComponentUsing = null;

    protected ?Closure $modifyDescriptionFormComponentUsing = null;

    /**
     * @var array<string, \Filament\Forms\Components\Field>
     */
    protected array $extraFormComponents = [];

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelFQCN
     * @return $this
     */
    public function viewTypeModel(string $modelFQCN): static
    {
        $this->viewTypeModel = $modelFQCN;

        return $this;
    }

    public function nameFormComponent(Closure $callback): static
    {
        $this->modifyNameFormComponentUsing = $callback;

        return $this;
    }

    public function iconFormComponent(Closure $callback): static
    {
        $this->modifyIconFormComponentUsing = $callback;

        return $this;
    }

    public function colorFormComponent(Closure $callback): static
    {
        $this->modifyColorFormComponentUsing = $callback;

        return $this;
    }

    public function isPublicFormComponent(Closure $callback): static
    {
        $this->modifyIsPublicFormComponentUsing = $callback;

        return $this;
    }

    public function isFavoriteFormComponent(Closure $callback): static
    {
        $this->modifyIsFavoriteFormComponentUsing = $callback;

        return $this;
    }

    public function isGloballyHighlightedFormComponent(Closure $callback): static
    {
        $this->modifyIsGloballyHighlightedFormComponentUsing = $callback;

        return $this;
    }

    public function descriptionFormComponent(Closure $callback): static
    {
        $this->modifyDescriptionFormComponentUsing = $callback;

        return $this;
    }

    public function extraFormComponent(string $beforeComponent, Field $component): static
    {
        $this->extraFormComponents[$beforeComponent] = $component;

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
     * @return list<\Filament\Forms\Components\Field | \Filament\Forms\Components\Component>
     */
    public function getFormComponents(): array
    {
        $components = array_filter(
            $this->getDefaultFormComponents()
        );

        if (blank($this->extraFormComponents)) {
            return $components;
        }

        $newComponents = [];

        foreach ($components as $component) {
            if ($component instanceof Field) {
                $extraComponent = $this->getExtraFormComponentBefore($component->getName());

                if ($extraComponent !== null) {
                    $newComponents[] = $extraComponent;
                }
            }

            $newComponents[] = $component;
        }

        return array_filter($newComponents);
    }

    /**
     * @return list<\Filament\Forms\Components\Field | \Filament\Forms\Components\Component>
     */
    public function getDefaultFormComponents(): array
    {
        return [
            $this->getNameFormComponent(),
            $this->getIconFormComponent(),
            $this->getColorFormComponent(),
            $this->getIsPublicFormComponent(),
            $this->getIsFavoriteFormComponent(),
            $this->getIsGloballyHighlightedFormComponent(),
            $this->getDescriptionFormComponent(),
        ];
    }

    public function getNameFormComponent(): ?Field
    {
        $component = TextInput::make('name')
            ->label(__('filament-table-views::toolbar.actions.table-view-action.form.name'))
            ->maxLength(64)
            ->required();

        if ($this->modifyNameFormComponentUsing) {
            $component = $this->evaluate($this->modifyNameFormComponentUsing, [
                'field' => $component,
                'component' => $component,
            ], [
                TextInput::class => $component,
            ]) ?? null;
        }

        return $component;
    }

    public function getIconFormComponent(): ?Field
    {
        $component = Hidden::make('icon')
            ->label(__('filament-table-views::toolbar.actions.table-view-action.form.icon'));

        if ($this->modifyIconFormComponentUsing) {
            $component = $this->evaluate($this->modifyIconFormComponentUsing, [
                'field' => $component,
                'component' => $component,
            ], [
                Hidden::class => $component,
            ]) ?? null;
        }

        return $component;
    }

    public function getColorFormComponent(): ?Field
    {
        $component = ColorPicker::make('color')
            ->label(__('filament-table-views::toolbar.actions.table-view-action.form.color'));

        if ($this->modifyColorFormComponentUsing) {
            $component = $this->evaluate($this->modifyColorFormComponentUsing, [
                'field' => $component,
                'component' => $component,
            ], [
                ColorPicker::class => $component,
            ]) ?? null;
        }

        return $component;
    }

    public function getIsPublicFormComponent(): ?Field
    {
        $component = Toggle::make('is_public')
            ->label(__('filament-table-views::toolbar.actions.table-view-action.form.is_public'))
            ->default(true);

        if ($this->modifyIsPublicFormComponentUsing) {
            $component = $this->evaluate($this->modifyIsPublicFormComponentUsing, [
                'field' => $component,
                'component' => $component,
            ], [
                Toggle::class => $component,
            ]) ?? null;
        }

        return $component;
    }

    public function getIsFavoriteFormComponent(): ?Field
    {
        $component = Toggle::make('is_favorite')
            ->label(__('filament-table-views::toolbar.actions.table-view-action.form.is_favorite'));

        if ($this->modifyIsFavoriteFormComponentUsing) {
            $component = $this->evaluate($this->modifyIsFavoriteFormComponentUsing, [
                'field' => $component,
                'component' => $component,
            ], [
                Toggle::class => $component,
            ]) ?? null;
        }

        return $component;
    }

    public function getIsGloballyHighlightedFormComponent(): ?Field
    {
        $component = Toggle::make('is_globally_highlighted')
            ->label(__('filament-table-views::toolbar.actions.table-view-action.form.is_globally_highlighted'));

        if ($this->modifyIsGloballyHighlightedFormComponentUsing) {
            $component = $this->evaluate($this->modifyIsGloballyHighlightedFormComponentUsing, [
                'field' => $component,
                'component' => $component,
            ], [
                Toggle::class => $component,
            ]) ?? null;
        }

        return $component;
    }

    public function getDescriptionFormComponent(): ?Field
    {
        $component = Textarea::make('description')
            ->label(__('filament-table-views::toolbar.actions.table-view-action.form.description'))
            ->maxLength(65535);

        if ($this->modifyDescriptionFormComponentUsing) {
            $component = $this->evaluate($this->modifyDescriptionFormComponentUsing, [
                'field' => $component,
                'component' => $component,
            ], [
                Textarea::class => $component,
            ]) ?? null;
        }

        return $component;
    }

    protected function getExtraFormComponentBefore(string $componentName): ?Field
    {
        return $this->extraFormComponents[$componentName] ?? null;
    }
}
