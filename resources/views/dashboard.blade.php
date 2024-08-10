<x-app-layout>
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
                            <div class="flex-grow-0 items-center space-x-3">
                                <h3 class="truncate text-sm font-medium text-gray-900">{{ $shell->title }}</h3>
                            </div>
                            <p class="flex-grow mt-1 truncate text-sm text-gray-500">
                                <strong>Folder:</strong> {{ \Illuminate\Support\Str::limit($shell->path ?? '-', 55) }}
                            </p>
                            <p class="flex-grow mt-1 pt-4 truncate text-sm text-gray-500">
                                @if ($shell->is_docker_context)
                                    <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="none" class="w-5"><path fill="#2396ED" d="M12.342 4.536l.15-.227.262.159.116.083c.28.216.869.768.996 1.684.223-.04.448-.06.673-.06.534 0 .893.124 1.097.227l.105.057.068.045.191.156-.066.2a2.044 2.044 0 01-.47.73c-.29.299-.8.652-1.609.698l-.178.005h-.148c-.37.977-.867 2.078-1.702 3.066a7.081 7.081 0 01-1.74 1.488 7.941 7.941 0 01-2.549.968c-.644.125-1.298.187-1.953.185-1.45 0-2.73-.288-3.517-.792-.703-.449-1.243-1.182-1.606-2.177a8.25 8.25 0 01-.461-2.83.516.516 0 01.432-.516l.068-.005h10.54l.092-.007.149-.016c.256-.034.646-.11.92-.27-.328-.543-.421-1.178-.268-1.854a3.3 3.3 0 01.3-.81l.108-.187zM2.89 5.784l.04.007a.127.127 0 01.077.082l.006.04v1.315l-.006.041a.127.127 0 01-.078.082l-.039.006H1.478a.124.124 0 01-.117-.088l-.007-.04V5.912l.007-.04a.127.127 0 01.078-.083l.039-.006H2.89zm1.947 0l.039.007a.127.127 0 01.078.082l.006.04v1.315l-.007.041a.127.127 0 01-.078.082l-.039.006H3.424a.125.125 0 01-.117-.088L3.3 7.23V5.913a.13.13 0 01.085-.123l.039-.007h1.413zm1.976 0l.039.007a.127.127 0 01.077.082l.007.04v1.315l-.007.041a.127.127 0 01-.078.082l-.039.006H5.4a.124.124 0 01-.117-.088l-.006-.04V5.912l.006-.04a.127.127 0 01.078-.083l.039-.006h1.413zm1.952 0l.039.007a.127.127 0 01.078.082l.007.04v1.315a.13.13 0 01-.085.123l-.04.006H7.353a.124.124 0 01-.117-.088l-.006-.04V5.912l.006-.04a.127.127 0 01.078-.083l.04-.006h1.412zm1.97 0l.039.007a.127.127 0 01.078.082l.006.04v1.315a.13.13 0 01-.085.123l-.039.006H9.322a.124.124 0 01-.117-.088l-.006-.04V5.912l.006-.04a.127.127 0 01.078-.083l.04-.006h1.411zM4.835 3.892l.04.007a.127.127 0 01.077.081l.007.041v1.315a.13.13 0 01-.085.123l-.039.007H3.424a.125.125 0 01-.117-.09l-.007-.04V4.021a.13.13 0 01.085-.122l.039-.007h1.412zm1.976 0l.04.007a.127.127 0 01.077.081l.007.041v1.315a.13.13 0 01-.085.123l-.039.007H5.4a.125.125 0 01-.117-.09l-.006-.04V4.021l.006-.04a.127.127 0 01.078-.082l.039-.007h1.412zm1.953 0c.054 0 .1.037.117.088l.007.041v1.315a.13.13 0 01-.085.123l-.04.007H7.353a.125.125 0 01-.117-.09l-.006-.04V4.021l.006-.04a.127.127 0 01.078-.082l.04-.007h1.412zm0-1.892c.054 0 .1.037.117.088l.007.04v1.316a.13.13 0 01-.085.123l-.04.006H7.353a.124.124 0 01-.117-.088l-.006-.04V2.128l.006-.04a.127.127 0 01.078-.082L7.353 2h1.412z"/></svg>
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
</x-app-layout>
