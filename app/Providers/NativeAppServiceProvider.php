<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Native\Laravel\Facades\ContextMenu;
use Native\Laravel\Facades\Dock;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Window;
use Native\Laravel\Facades\GlobalShortcut;
use Native\Laravel\Menu\Menu;

class NativeAppServiceProvider
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Artisan::call('migrate --force');

        Menu::new()
            // ->appMenu()
            ->submenu('About', Menu::new()
                ->link('https://jackedphp.com', 'Jacked PHP')
            )
            ->submenu('View', Menu::new()
                ->toggleFullscreen()
            )
            ->register();

        logger()->info('server', [
            'app' => config('app'),
            'native' => config('nativephp'),
        ]);
        MenuBar::create()
            // ->label('Psy REPL')
            ->showDockIcon()
            ->icon(storage_path('app/public/logo-bar.png'))
            ->onlyShowContextMenu()
            ->withContextMenu(
                Menu::new()->quit()
            );

        Window::open()
            // ->titleBarHidden()
            ->width(1200)
            ->height(800)
            ->rememberState();

        // Dock::menu(
        //     Menu::new()
        //         ->event(DockItemClicked::class, 'Settings')
        //         ->submenu('Help',
        //             Menu::new()
        //                 ->event(DockItemClicked::class, 'About')
        //                 ->event(DockItemClicked::class, 'Learn More…')
        //         )
        // );

        ContextMenu::register(Menu::new());

        // GlobalShortcut::new()
        //     ->key('CmdOrCtrl+Shift+I')
        //     ->event(ShortcutPressed::class)
        //     ->register();
    }
}
