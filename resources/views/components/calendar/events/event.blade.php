<div class="py-0.5 px-2" role="region" aria-labelledby="event-details">

    <template x-if="view.type === 'resourceTimeGridWeek' && event.title !== 'Break Time'">
        <span x-html="event.extendedProps.icon"></span>
    </template>

    <template x-if="view.type === 'dayGridMonth' && event.title !== 'Break Time'">
        <span x-html="event.extendedProps.icon" class="svg-container"></span>
    </template>

    <template x-if="view.type === 'resourceTimeGridDay'">
        <div class="flex flex-col flex-wrap" id="event-details">
            <div class="flex flex-row justify-between">
                <div class="flex flex-col items-start">
                    <span x-text="timeText" class="font-semibold text-md"></span>

                    <div class="flex items-center">
                        <template x-if="event.title !== 'Break Time'">
                            <span x-text="event.extendedProps.status" class="text-sm"></span>
                        </template>

                        <template x-if="event.title !== 'Break Time'">
                            <span x-html="event.extendedProps.icon"></span>
                        </template>
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <span x-text="event.title" class="text-xs text-end" style="width: 150px"></span>
                    <template x-if="event.title !== 'Break Time'">
                    <span class="text-base font-semibold">
                        <span x-text="event.extendedProps.total_price || 0"></span> MKD
                    </span>
                    </template>
                </div>
            </div>
            <div class="flex">
                <template x-if="event.title !== 'Break Time'">
                        <span>
                            Client:
                            <span class="font-semibold text-md" x-text="event.extendedProps.client || 'N/A'"></span>
                        </span>
                </template>
                <template x-if="event.title !== 'Break Time'">
                        <span class="px-2">
                            Assigned User:
                            <span class="font-semibold text-md" x-text="event.extendedProps.assigned_user || 'N/A'"></span>
                        </span>
                </template>
            </div>
        </div>
    </template>
</div>

<style>
    .svg-container svg {
        width: 10px;
        height: 10px;
    }
</style>
