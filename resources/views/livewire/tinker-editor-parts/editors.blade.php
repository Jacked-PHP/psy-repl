<div
    :class="displayHorizontal ? 'flex-row' : 'flex-col'"
    class="flex flex-grow bg-gray-200 drop-shadow-md w-full overflow-hidden"
    x-cloak
>
    {{-- Code --}}
    <div
        :class="displayHorizontal ? 'w-1/2' : 'w-auto max-h-1/2'"
        class="flex flex-col flex-grow @if($editorType === 'codemirror') overflow-auto @endif"
    >
        <div class="bg-[#192d52] text-white text-xs text-center flex justify-between py-2 flex-1 h-[32px" :class="displayHorizontal ? 'border-r border-r-gray-500' : ''">
            <span class="w-14"></span>
            <span>Code</span>
            <div class="w-6 pr-2 flex gap-2">
            {{--<div class="w-14 pr-2 flex gap-2">--}}
                {{--<button @click="displayHorizontal = !displayHorizontal" type="submit" class="rounded rounded-md">
                    --}}{{-- vertical --}}{{--
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" :class="!displayHorizontal ? 'hidden' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                    </svg>

                    --}}{{-- horizontal --}}{{--
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" :class="displayHorizontal ? 'hidden' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                </button>--}}

                <button @click="$store.editor.toggleMinimalMode()" class="rounded rounded-md">
                    {{-- not minimal --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" :class="$store.editor.minimalMode ? 'hidden' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>

                    {{-- minimal --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" :class="!$store.editor.minimalMode ? 'hidden' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25" />
                    </svg>
                </button>
            </div>
        </div>
        @if ($editorType === 'monaco')
            {{--<div
                wire:ignore
                class="code-content border-0 flex-grow bg-[#282828]"
                :class="displayHorizontal ? 'max-w-1/2 h-auto max-h-full' : 'max-h-1/2 w-auto'"
                x-ref="editor"
            ></div>--}}
        @elseif($editorType === 'ace')
            <div
                wire:ignore
                class="ace-editor code-content border-0 flex-grow bg-[#282828] overflow-auto text-xl"
                :class="displayHorizontal ? 'max-w-1/2 h-full max-h-full' : 'max-h-1/2 w-auto'"
                x-ref="editor"
            ></div>
        @else
            {{--<div
                wire:ignore
                class="code-content border-0 flex-grow bg-[#282828] overflow-auto"
                :class="displayHorizontal ? 'max-w-1/2 h-full max-h-full' : 'max-h-1/2 w-auto'"
                x-ref="editor"
            ></div>--}}
        @endif
    </div>

    {{-- Output --}}
    <div
        :class="displayHorizontal ? 'w-1/2' : 'w-auto max-h-1/2'"
        class="flex flex-col flex-grow"
        wire:ignore
    >
        <div class="bg-[#192d52] text-white text-xs flex justify-between text-center py-2 flex-1 h-[32px]">
            <div class="w-14 pl-2 flex gap-2">
                <button @click="executeCode()" class="rounded rounded-md flex gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                    </svg>
                    <span class="text-[10px]">[Ctrl+Shift+Enter]</span>
                </button>
            </div>
            <span>Output</span>
            <span class="w-14"></span>
        </div>
        @if ($editorType === 'monaco')
            {{--<div
                wire:ignore x-ref="output"
                class="output-content bg-[#282828] flex-grow"
                :class="displayHorizontal ? 'max-w-1/2 h-auto max-h-full' : 'max-h-1/2 w-auto'"
            ></div>--}}
        @elseif($editorType === 'ace')
            <div
                wire:ignore x-ref="output"
                class="ace-editor output-content bg-[#282828] flex-grow overflow-auto text-xl"
                :class="displayHorizontal ? 'max-w-1/2 h-full max-h-full' : 'max-h-1/2 w-auto'"
            ></div>
        @else
            {{-- codemirror --}}
            {{--<div
                wire:ignore x-ref="output"
                class="output-content bg-[#282828] flex-grow overflow-auto"
                :class="displayHorizontal ? 'max-w-1/2 h-full max-h-full' : 'max-h-1/2 w-auto'"
            ></div>--}}
        @endif
    </div>
</div>
