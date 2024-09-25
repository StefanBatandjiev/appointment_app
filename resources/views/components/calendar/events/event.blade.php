<div class="py-0.5 px-2" role="region" aria-labelledby="event-details">

    <template x-if="view.type === 'resourceTimeGridWeek' && event.title !== 'Break Time'">
        <img
            :src="event.extendedProps.icon"
            width="25"
            height="25"
            :style="event.extendedProps.iconColor"
            alt="Status Icon"
        />
    </template>

    <template x-if="view.type === 'dayGridMonth' && event.title !== 'Break Time'">
        <img
            :src="event.extendedProps.icon"
            width="5"
            height="5"
            :style="event.extendedProps.iconColor"
            alt="Status Icon"
        />
    </template>

    <template x-if="view.type === 'resourceTimeGridDay'">
        <div class="flex flex-row flex-wrap justify-between" id="event-details">
            <div class="flex flex-col justify-between">
                <div class="flex flex-row items-center">
                    <span x-text="timeText" class="font-semibold text-md"></span>

                    <template x-if="event.title !== 'Break Time'">
                        <span x-text="event.extendedProps.status" class="text-sm"
                              style="padding-left: 20px; padding-right: 5px"></span>
                    </template>

                    <template x-if="event.title !== 'Break Time'">
                        <img
                            :src="event.extendedProps.icon"
                            width="25"
                            height="25"
                            :style="event.extendedProps.iconColor"
                            alt="Status Icon"
                        />
                    </template>

                </div>
                <template x-if="event.title !== 'Break Time'">
                    <span>
                    Client:
                    <span class="font-semibold text-md" x-text="event.extendedProps.client || 'N/A'"></span>
                </span>
                </template>
                <template x-if="event.title !== 'Break Time'">
                    <span>
                    Assigned User:
                    <span class="font-semibold text-md" x-text="event.extendedProps.assigned_user || 'N/A'"></span>
                </span>
                </template>
            </div>
            <div class="flex flex-col items-end">
                <span x-text="event.title" class="text-xs"></span>
                <template x-if="event.title !== 'Break Time'">
                    <span class="text-base font-semibold">
                        <span x-text="event.extendedProps.total_price || 0"></span> MKD
                    </span>
                </template>
            </div>
        </div>
    </template>
</div>

