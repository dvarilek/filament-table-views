<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Actions;

use Closure;
use Dvarilek\FilamentTableViews\Components\Table\TableView;
use Dvarilek\FilamentTableViews\Contracts\HasTableViewOwnership;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Contracts\HasTable;

class EditTableViewAction extends TableViewAction
{
    /**
     * @var Closure(\Filament\Notifications\Notification, \Dvarilek\FilamentTableViews\Components\Table\TableView): \Filament\Notifications\Notification | null
     */
    protected ?Closure $modifyAfterTableViewUpdatedNotificationUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'editTableView';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-table-views::toolbar.actions.edit-table-view.label'));

        $this->modalDescription(__('filament-table-views::toolbar.actions.edit-table-view.description'));

        $this->modalSubmitActionLabel(__('filament-table-views::toolbar.actions.edit-table-view.submit_label'));

        $this->iconButton();

        $this->icon('heroicon-o-pencil');

        $this->color('primary');

        $this->slideOver();

        $this->modalWidth(MaxWidth::Medium);

        $this->form(static fn (EditTableViewAction $action) => $action->getFormComponents());

        $this->action(static function (EditTableViewAction $action, HasTable $livewire, array $data): void {
            $viewTypeModel = $action->getViewTypeModel();

            if (! $viewTypeModel) {
                throw new \Exception('The EditViewAction must have a viewTypeModel set.');
            }

            /* @var \Illuminate\Contracts\Auth\Authenticatable | null $user */
            $user = auth()->user();

            if (! $user) {
                throw new \Exception('Cannot edit TableView, user not found.');
            }

            if (! is_subclass_of($user::class, HasTableViewOwnership::class)) {
                throw new \Exception('User class '.$user::class.' must implement '.HasTableViewOwnership::class);
            }

            if ($data['should_update_view'] ?? null) {
                $data['query_constraints'] = $action->extractQueryConstraints($livewire);
            }

            unset($data['should_update_view']);

            /* @var TableView $tableView */
            $tableView = $user->tableViews()->create([
                ...$data,
                'model_type' => $viewTypeModel,
            ]);

            $notification = $this->getAfterTableViewUpdatedNotification();

            if ($this->modifyAfterTableViewUpdatedNotificationUsing) {
                $notification = ($this->modifyAfterTableViewUpdatedNotificationUsing)($notification, $tableView);
            }

            $notification->send();
        });
    }

    /**
     * @param  Closure(\Filament\Notifications\Notification, \Dvarilek\FilamentTableViews\Components\Table\TableView): \Filament\Notifications\Notification  $callback
     * @return $this
     */
    public function afterTableViewUpdatedNotification(Closure $callback): static
    {
        $this->modifyAfterTableViewUpdatedNotificationUsing = $callback;

        return $this;
    }

    public function getAfterTableViewUpdatedNotification(): Notification
    {
        return Notification::make('filament-table-views::after_table_view_updated-notification')
            ->title(__('filament-table-views::toolbar.actions.edit-table-view.notifications.after_table_view_updated.title'))
            ->success();
    }

    public function getDefaultFormFields(): array
    {
        $components = parent::getDefaultFormFields();

        $components[] = Toggle::make('should_update_view')
            ->label(__('filament-table-views::toolbar.actions.edit-table-view.notifications.should_update_view'));

        return $components;
    }
}
