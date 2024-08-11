<div class="border-t border-t-gray-100">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Shells') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

                <li class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
                    <a href="{{ route('shells.show', ['shell' => null]) }}" class="flex w-full h-full items-center justify-center space-x-6 p-6 bg-white hover:bg-gray-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <h3 class="truncate font-medium text-gray-900 text-xl">New</h3>
                    </a>
                </li>

                @foreach($shells as $shell)
                    <li class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow flex flex-col">
                        <div class="flex flex-col flex-grow w-full items-start justify-between pt-6 pl-6 pr-6 pb-2 truncate">
                            <div class="flex-grow-0 items-center space-x-3 flex justify-between w-full">
                                <h3 class="truncate text-sm font-medium text-gray-900">{{ $shell->title }}</h3>

                                <a class="cursor-pointer hover:bg-red-200 p-2 rounded" @click="confirm('Are you sure you want to delete the Shell \'{{ $shell->title  }}\'?') ? $wire.deleteShell({{ $shell->id }}) : null"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg></a>
                            </div>
                            <p class="flex-grow mt-1 truncate text-sm text-gray-500">
                                <strong>Folder:</strong> {{ \Illuminate\Support\Str::limit($shell->path ?? '-', 55) }}
                            </p>
                            <p class="flex-grow mt-1 pt-4 truncate text-sm text-gray-500">
                                @if ($shell->isDockerContext)
                                    <x-docker-icon />
                                @elseif ($shell->isRemoteContext)
                                    <x-ssh-icon color="#11669e" />
                               @else
                                    <svg class="w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.5 2C6.67157 2 5.25736 2 4.37868 2.87868C3.5 3.75736 3.5 5.17157 3.5 8V11C3.5 12.8856 3.5 13.8284 4.08579 14.4142C4.67157 15 5.61438 15 7.5 15H16.5C18.3856 15 19.3284 15 19.9142 14.4142C20.5 13.8284 20.5 12.8856 20.5 11V8C20.5 5.17157 20.5 3.75736 19.6213 2.87868C18.7426 2 17.3284 2 14.5 2H14" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"/><path d="M12 22H5C3.34315 22 2 20.6569 2 19V18C2 17.4477 2.44772 17 3 17H7.33333C7.76607 17 8.18714 17.1404 8.53333 17.4L9.46667 18.1C9.81286 18.3596 10.2339 18.5 10.6667 18.5H13.3333C13.7661 18.5 14.1871 18.3596 14.5333 18.1L15.4667 17.4C15.8129 17.1404 16.2339 17 16.6667 17H21C21.5523 17 22 17.4477 22 18V19C22 20.6569 20.6569 22 19 22H16" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round"/></svg>
                               @endif
                            </p>
                        </div>
                        <div class=" flex-grow-0">
                            <div class="-mt-px flex divide-x divide-gray-200">
                                <div class="-ml-px flex w-0 flex-1">
                                    <a href="{{ route('shells.show', ['shell' => $shell->id]) }}"
                                       class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold text-gray-900 hover:bg-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-gray-400"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                                        Open
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

        </div>
    </div>
</div>
