<div class="flex flex-col py-0.5 px-2">
    <template x-if="view.type === 'resourceTimeGridDay'">
        <div>
            <div class="flex flex-row justify-between">
                <span x-text="timeText" class="font-semibold text-md"></span>
                <span x-text="event.title" class="text-xs"></span>
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

