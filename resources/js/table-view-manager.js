export default function tableViewManager({
    defaultCollapsedGroups,
    isDeferredReorderable,
    isMultiGroupReorderable,
    isHighlightingReorderedRecords
}) {
    return {
        collapsedGroups: new Set(defaultCollapsedGroups),

        pendingReorderedRecords: new Map(),

        activeReorderingGroup: null,

        isMultiGroupReorderingActive: false,

        reorderedRecords: new Set(),

        isLoading: false,

        toggleCollapsedGroup(group) {
            if (this.isGroupCollapsed(group)) {
                this.collapsedGroups.delete(group)

                return
            }

            this.collapsedGroups.add(group)
        },

        isGroupCollapsed: function (group) {
            return this.collapsedGroups.has(group)
        },

        isReorderingGroup(group = null) {
            if (isMultiGroupReorderable) {
                return this.isMultiGroupReorderingActive;
            }

            return this.activeReorderingGroup === group
        },

        isReorderingActive() {
            if (isMultiGroupReorderable) {
                return this.isMultiGroupReorderingActive;
            }

            return this.activeReorderingGroup !== null;
        },

        startReordering(group = null) {
            if (this.isLoading) {
                return
            }

            if (isMultiGroupReorderable) {
                this.isMultiGroupReorderingActive = true;

                return;
            }

            this.activeReorderingGroup = group;
        },

        stopReordering(group = null) {
            if (isMultiGroupReorderable) {
                this.isMultiGroupReorderingActive = false;

                return;
            }

            this.activeReorderingGroup = null;
        },

        hasPendingReorderedRecords(group = null) {
            if (group) {
                return this.pendingReorderedRecords.has(group)
            }

            return this.pendingReorderedRecords.size > 0
        },

        isRecordReordered(recordKey) {
            return this.reorderedRecords.has(recordKey)
        },

        addToReorderedRecords(event) {
            const recordKey = event.item.getAttribute('x-sortable-item')

            if (recordKey) {
                this.reorderedRecords.add(recordKey)
            }
        },

        async toggleReordering(group = null) {
            if (this.isLoading) {
                return
            }

            if (this.isReorderingGroup(group)) {
                if (isDeferredReorderable) {
                    if (isMultiGroupReorderable) {
                        await this.reorderMultipleGroups(this.pendingReorderedRecords)
                    } else {
                        await this.reorderGroup(group, this.pendingReorderedRecords.get(group))
                    }

                    this.pendingReorderedRecords.clear()

                    if (isHighlightingReorderedRecords) {
                        this.reorderedRecords.clear()
                    }
                }

                this.stopReordering(group)

                return
            }

            this.startReordering(group)
        },


        async handleGroupReorder(event) {
            if (event.oldIndex === event.newIndex) {
                return
            }

            const newOrder = new Set(event.target.sortable.toArray())
            const group = event.target.dataset.tableViewGroup

            if (isDeferredReorderable) {
                this.pendingReorderedRecords.set(group, newOrder)

                if (isHighlightingReorderedRecords) {
                    this.addToReorderedRecords(event)
                }

                return
            }

            await this.reorderGroup(group, newOrder)
        },

        async handleMultiGroupReorder(event) {
            const fromGroup = event.from.dataset.tableViewGroup
            const toGroup = event.to.dataset.tableViewGroup

            if (event.oldIndex === event.newIndex && fromGroup === toGroup) {
                return
            }

            const fromNewOrder = new Set(event.from.sortable.toArray())
            const toNewOrder = new Set(event.to.sortable.toArray())

            if (isDeferredReorderable) {
                this.pendingReorderedRecords.set(fromGroup, fromNewOrder)

                if (fromGroup !== toGroup) {
                    this.pendingReorderedRecords.set(toGroup, new Set(toNewOrder))
                }

                if (isHighlightingReorderedRecords) {
                    this.addToReorderedRecords(event)
                }

                return
            }

            await this.reorderMultipleGroups(new Map([
                [fromGroup, fromNewOrder],
                [toGroup, toNewOrder]
            ]))
        },

        async reorderGroup(group, order) {
            if (! order || ! order.size) {
                return
            }

            this.isLoading = true

            try {
                await this.$wire.reorderTableViewsInGroup(group, [...order])
            } finally {
                this.isLoading = false
            }
        },

        async reorderMultipleGroups(groupOrdersMap) {
            if (! groupOrdersMap || ! groupOrdersMap.size) {
                return
            }

            if (groupOrdersMap.size === 1) {
                await this.reorderGroup(...groupOrdersMap.entries().next().value)

                return
            }

            this.isLoading = true

            try {
                const groupedTableViewOrders = Object.fromEntries(
                    [...groupOrdersMap.entries()].map(([group, order]) => [group, [...order]])
                )

                await this.$wire.reorderTableViewsInGroups(groupedTableViewOrders)
            } finally {
                this.isLoading = false
            }
        }
    }
}
