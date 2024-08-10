<div x-ref="control" class="py-1 px-2 bg-white h-auto border-t border-t-gray-200 border-l border-l-gray-200 border-r border-r-gray-200 rounded rounded-tl-2xl rounded-tr-2xl mx-4 drop-shadow-md" :class="$store.editor.minimalMode ? 'hidden' : ''" x-cloak>
    {{-- Closed : BEGIN --}}
    <div x-ref="control-view-section" x-show="!formOpened" class="text-xs text-gray-700 px-2 flex justify-between cursor-pointer" @click="formOpened = !formOpened">
        <div class="flex justify-between items-center gap-4">
            <div x-text="title"></div>
            <div class="flex gap-2"><span class="font-bold">Path:</span><span x-text="path ?? '(not selected)'"></span></div>
            <template x-if="isDockerContext">
                <div class="flex gap-2"><span class="font-bold">Container:</span><span x-text="dockerContainer ?? '(not set)'"></span></div>
            </template>
            <svg wire:loading.remove x-show="!loading" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 text-green-700"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            <svg wire:loading x-show="loading" class="w-4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,20a9,9,0,1,1,9-9A9,9,0,0,1,12,21Z"/><rect x="11" y="6" rx="1" width="2" height="7"><animateTransform attributeName="transform" type="rotate" dur="9s" values="0 12 12;360 12 12" repeatCount="indefinite"/></rect><rect x="11" y="11" rx="1" width="2" height="9"><animateTransform attributeName="transform" type="rotate" dur="0.75s" values="0 12 12;360 12 12" repeatCount="indefinite"/></rect></svg>
        </div>
    </div>
    {{-- Closed : END --}}

    {{-- Opened : BEGIN --}}
    <div x-ref="control-form-section" class="flex flex-col gap-2 pb-2" x-show="formOpened">
        <div @click="formOpened = !formOpened" x-ref="control-view-section" class="flex justify-between cursor-pointer">
            <span class="w-14"></span>

            <div class="text-xs flex items-center gap-2">
                <span>Settings</span>
                <svg wire:loading.remove x-show="!loading" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 text-green-700"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                <svg wire:loading x-show="loading" class="w-4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,20a9,9,0,1,1,9-9A9,9,0,0,1,12,21Z"/><rect x="11" y="6" rx="1" width="2" height="7"><animateTransform attributeName="transform" type="rotate" dur="9s" values="0 12 12;360 12 12" repeatCount="indefinite"/></rect><rect x="11" y="11" rx="1" width="2" height="9"><animateTransform attributeName="transform" type="rotate" dur="0.75s" values="0 12 12;360 12 12" repeatCount="indefinite"/></rect></svg>
            </div>

            <div class="w-14 text-right">
                <button @click="formOpened = !formOpened" class="p-1 border rounded-md border-transparent hover:border-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <span class="w-32">Title:</span>
            <div class="relative text-gray-600 flex-grow">
                <input
                    wire:model.live="title"
                    type="text"
                    name="title"
                    class="bg-white h-8 w-full px-2 rounded-lg border text-sm focus:outline-none text-black"
                >
            </div>
        </label>

        <label class="flex items-center gap-2 text-sm">
            <span class="w-32">PHP Binary:</span>
            <div class="relative text-gray-600 flex-grow">
                <input
                    wire:model.live="php_binary"
                    x-ref="php_binary"
                    type="text"
                    name="php_binary"
                    placeholder="/usr/bin/php"
                    class="bg-white h-8 w-full px-2 rounded-lg border text-sm focus:outline-none text-black"
                >
            </div>
        </label>

        <label class="flex items-center gap-2 text-sm hidden">
            <span class="w-32">Shell Title:</span>
            <div class="relative text-gray-600 flex-grow">
                <input
                    wire:model.live="title"
                    type="text"
                    name="title"
                    placeholder="My Title"
                    class="bg-white h-8 w-full px-2 rounded-lg border text-sm focus:outline-none text-black"
                >
            </div>
        </label>

        {{-- input for path selection : BEGIN --}}
        <label class="flex items-center gap-2 w-full text-sm">
            <span class="w-32">Project's Folder:</span>
            <div class="relative text-gray-600 flex-grow">
                <input
                    wire:model.live="path"
                    x-ref="path"
                    type="search"
                    name="serch"
                    placeholder="/my/path"
                    class="bg-white h-8 w-full px-2 rounded-lg border text-sm focus:outline-none text-black"
                >
                <button wire:click="openFolderDialog" type="submit" class="absolute right-0 top-0 mt-1.5 mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                    </svg>
                </button>
            </div>
        </label>
        {{-- input for path selection : END --}}

        {{-- input for app selection : BEGIN --}}
        {{-- TODO <label class="flex items-center gap-2 w-full text-sm">
            <span class="w-32">Project's Folder:</span>
            <div class="relative text-gray-600 flex-grow">
                <input x-model.debounce.500ms="path" type="search" name="serch" placeholder="/my/path" class="bg-white h-8 w-full px-2 rounded-lg border text-sm focus:outline-none text-black">
                <button wire:click="openFolderDialog" type="submit" class="absolute right-0 top-0 mt-1.5 mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                    </svg>
                </button>
            </div>
        </label>--}}
        {{-- input for app selection : END --}}

        {{-- Docker : BEGIN --}}
        <label class="flex items-center gap-2 text-sm h-10">
            <div class="flex gap-2 items-center">
                <span class="w-32">Docker Context:</span>
                <input
                    wire:model.live="isDockerContext"
                    type="checkbox"
                />
            </div>
        </label>
        <template x-if="$wire.isDockerContext">
            <label class="flex items-center gap-2 text-sm h-10">
                <div class="flex gap-2 items-center">
                    <span class="w-32">Container name:</span>
                </div>
                <div class="relative text-gray-600 flex-grow">
                    <input
                        wire:model.live="dockerContainer"
                        type="text"
                        name="dockerContainer"
                        placeholder="php"
                        class="bg-white h-8 w-full px-2 rounded-lg border text-sm focus:outline-none text-black"
                    >
                </div>
            </label>
        </template>
        <template x-if="$wire.isDockerContext">
            <label class="flex items-center gap-2 text-sm h-10">
                <div class="flex gap-2 items-center">
                    <span class="w-32">Docker Workdir:</span>
                </div>
                <div class="relative text-gray-600 flex-grow">
                    <input
                        wire:model.live="dockerWorkdir"
                        type="text"
                        name="dockerWorkdir"
                        placeholder="/var/www"
                        class="bg-white h-8 w-full px-2 rounded-lg border text-sm focus:outline-none text-black"
                    >
                </div>
            </label>
        </template>
        {{-- Docker : END --}}
    </div>
    {{-- Opened : END --}}
</div>
