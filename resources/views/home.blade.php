<x-pre-app-layout>
    <div class="h-screen w-screen flex flex-col justify-center items-center">


        <h1 class="text-2xl mb-4">Welcome to <strong>Psy REPL!</strong></h1>

        <div class="border-t border-black">
            <a href="{{ route('login') }}">Start Here</a>
        </div>
    </div>

    <div class="fixed w-screen bottom-2">
        {{--<img class="max-w-8 max-h-8" src="/images/jacked-php.png">--}}
        <a href="https://jackedphp.com" class="flex justify-center items-center text-xs">
            <img class="max-w-8 max-h-8 mr-2" src="/images/jackedphp-elephant-big-2.png">
            <span>A <span class="underline font-semibold">JackedPHP</span> Product</span>
        </a>
    </div>
</x-pre-app-layout>
