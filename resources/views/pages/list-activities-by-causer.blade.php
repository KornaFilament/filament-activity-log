<x-filament-panels::page>
    <div class="space-y-6">
        @foreach($this->getActivities() as $activityItem)

            @php
                /* @var \Spatie\Activitylog\Models\Activity $activityItem */
                $changes = $activityItem->attribute_changes ?? collect();
                $resource = $this->getSubjectResource($activityItem->subject_type);
            @endphp

            <div @class([
                'p-2 space-y-2 bg-white rounded-xl shadow',
                'dark:border-gray-600 dark:bg-gray-800',
            ])>
                <div class="p-2">
                    <div class="flex justify-between">
                        <div class="flex flex-col text-start">
                            <span class="font-bold">
                                @if ($resource)
                                    {{ $resource::getModelLabel() }}:
                                    @if ($activityItem->subject && $resource::hasRecordTitle())
                                        @php
                                            $url = $activityItem->subject->exists
                                                ? $resource::getUrl('edit', ['record' => $activityItem->subject])
                                                : null;
                                        @endphp
                                        @if ($url)
                                            <a href="{{ $url }}" class="underline">{{ $resource::getRecordTitle($activityItem->subject) }}</a>
                                        @else
                                            {{ $resource::getRecordTitle($activityItem->subject) }}
                                        @endif
                                    @else
                                        {{ $activityItem->subject_id }}
                                    @endif
                                @else
                                    {{ $activityItem->subject_type }}: {{ $activityItem->subject_id }}
                                @endif
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ __('filament-activity-log::activities.events.' . $activityItem->event) }} {{ $activityItem->created_at->format(__('filament-activity-log::activities.default_datetime_format')) }}
                            </span>
                        </div>
                    </div>
                </div>

                @if ($changes->isNotEmpty())
                    <table class="fi-ta-table w-full overflow-hidden text-sm">
                        <thead>
                            <th class="fi-ta-header-cell">
                                {{ __('filament-activity-log::activities.table.field') }}
                            </th>
                            <th class="fi-ta-header-cell">
                                {{ __('filament-activity-log::activities.table.old') }}
                            </th>
                            <th class="fi-ta-header-cell">
                                {{ __('filament-activity-log::activities.table.new') }}
                            </th>
                        </thead>

                        <tbody>
                            @foreach (data_get($changes, 'attributes', []) as $field => $change)
                                @php
                                    $oldValue = data_get($changes, "old.{$field}", '');
                                    $newValue = data_get($changes, "attributes.{$field}", '');
                                @endphp
                            <tr
                                @class([
                                    'fi-ta-row',
                                    'bg-gray-100/30' => $loop->even
                                ])
                            >
                                <td class="fi-ta-cell px-4 py-2 align-top sm:first-of-type:ps-6 sm:last-of-type:pe-6" width="20%">
                                    {{ $this->getFieldLabel($resource, $field) }}
                                </td>
                                <td width="40%" class="fi-ta-cell px-4 py-2 align-top break-all whitespace-normal">
                                    @if(is_array($oldValue))
                                        <pre class="text-xs text-gray-500">{{ json_encode($oldValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    @elseif (is_bool($oldValue))
                                        <span class="text-xs text-gray-500">{{ $oldValue ? 'true' : 'false' }}</span>
                                    @else
                                        {{ $oldValue }}
                                    @endif
                                </td>
                                <td width="40%" class="fi-ta-cell px-4 py-2 align-top break-all whitespace-normal">
                                    @if (is_bool($newValue))
                                        <span class="text-xs text-gray-500">{{ $newValue ? 'true' : 'false' }}</span>
                                    @elseif(is_array($newValue))
                                        <pre class="text-xs text-gray-500">{{ json_encode($newValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    @else
                                        {{ $newValue }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endforeach

        <x-filament::pagination
            currentPageOptionProperty="recordsPerPage"
            :page-options="$this->getRecordsPerPageSelectOptions()"
            :paginator="$this->getActivities()"
        />
    </div>
</x-filament-panels::page>
