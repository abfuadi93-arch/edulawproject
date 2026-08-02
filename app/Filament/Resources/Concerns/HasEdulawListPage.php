<?php

namespace App\Filament\Resources\Concerns;

use Closure;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;

trait HasEdulawListPage
{
    abstract protected function getListDescription(): string;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getSubheading(): ?string
    {
        return $this->getListDescription();
    }

    /**
     * @param  array<string, array{label: string, query?: Closure(Builder): Builder}>  $definitions
     * @return array<string, Tab>
     */
    protected function makeStatusTabs(array $definitions): array
    {
        $resource = static::getResource();
        $baseQuery = $resource::getEloquentQuery();

        return collect($definitions)
            ->mapWithKeys(function (array $definition, string $key) use ($baseQuery): array {
                $modifyQuery = $definition['query'] ?? null;
                $countQuery = clone $baseQuery;

                if ($modifyQuery) {
                    $modifyQuery($countQuery);
                }

                $tab = Tab::make($definition['label'])
                    ->badge($countQuery->count());

                if ($modifyQuery) {
                    $tab->modifyQueryUsing($modifyQuery);
                }

                return [$key => $tab];
            })
            ->all();
    }
}
