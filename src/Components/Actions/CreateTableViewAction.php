<?php

declare(strict_types=1);

namespace Dvarilek\FilamentTableViews\Components\Actions;

use Closure;
use Dvarilek\FilamentTableViews\Contracts\HasTableViewOwnership;
use Dvarilek\FilamentTableViews\DTO\TableViewState;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Contracts\HasTable;

class CreateTableViewAction extends TableViewAction
{
    /**
     * @var Closure(\Filament\Notifications\Notification, \Dvarilek\FilamentTableViews\Components\Table\TableView): \Filament\Notifications\Notification | null
     */
    protected ?Closure $modifyAfterTableViewCreatedNotificationUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'createTableView';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-table-views::toolbar.actions.create-table-view.label'));

        $this->modalDescription(__('filament-table-views::toolbar.actions.create-table-view.description'));

        $this->modalSubmitActionLabel(__('filament-table-views::toolbar.actions.create-table-view.submit_label'));

        $this->iconButton();

        $this->icon('heroicon-o-plus');

        $this->color('gray');

        $this->slideOver();

        $this->modalWidth(MaxWidth::Medium);

        $this->form(static fn (CreateTableViewAction $action) => $action->getFormComponents());

        $this->action(static function (CreateTableViewAction $action, HasTable $livewire, array $data): void {
            $viewModelType = $action->getModel();

            if (! $viewModelType) {
                throw new \Exception('The CreateViewAction must have a viewTypeModel set.');
            }

            /* @var \Illuminate\Contracts\Auth\Authenticatable | null $user */
            $user = auth()->user();

            if (! $user) {
                throw new \Exception('Cannot create TableView, user not found.');
            }

            if (! is_subclass_of($user::class, HasTableViewOwnership::class)) {
                throw new \Exception('User class '.$user::class.' must implement '.HasTableViewOwnership::class);
            }

            /* @var \DvarilekFilamentTableViewsComponentsTableTableView $tableView */
            $tableView = $user->tableViews()->create([
                ...$data,
                'model_type' => $viewModelType,
                'view_state' => TableViewState::fromLivewire($livewire),
            ]);

            $notification = $this->getAfterTableViewCreatedNotification();

            if ($this->modifyAfterTableViewCreatedNotificationUsing) {
                $notification = ($this->modifyAfterTableViewCreatedNotificationUsing)($notification, $tableView);
            }

            $notification->send();
        });
    }

    /**
     * @param  Closure(\Filament\Notifications\Notification $notification, \Dvarilek\FilamentTableViews\Components\Table\TableView $tableView): \Filament\Notifications\Notification  $callback
     * @return $this
     */
    public function afterTableViewCreatedNotification(Closure $callback): static
    {
        $this->modifyAfterTableViewCreatedNotificationUsing = $callback;

        return $this;
    }

    public function getAfterTableViewCreatedNotification(): Notification
    {
        return Notification::make('filament-table-views::after_table_view_created-notification')
            ->title(__('filament-table-views::toolbar.actions.create-table-view.notifications.after_table_view_created.title'))
            ->success();
    }
}
