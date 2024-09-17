<div class="flex flex-col py-0.5 px-2">
    <template x-if="view.type === 'resourceTimeGridDay'">
        <div>
            <div class="flex flex-row justify-between">
                <span x-text="timeText" class="font-semibold text-md"></span>
                <div class="flex flex-col items-end">
                    <span x-text="event.title" class="text-xs"></span>
                    <template x-if="event.title !== 'Break Time'">
                        <span class="text-base font-semibold"><span x-text="event.extendedProps.total_price" ></span> MKD</span>
                    </template>
                </div>
            </div>

            <template x-if="event.title !== 'Break Time'">
                <div class="flex flex-col justify-between pt-1">
                    <span>Client:  <span class="font-semibold text-md"
                                         x-text="event.extendedProps.client"></span></span>
                    <span>By user:  <span class="font-semibold text-md" x-text="event.extendedProps.user"></span></span>
                </div>
            </template>
        </div>
    </template>
</div>

