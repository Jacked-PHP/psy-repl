<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Native\Laravel\Facades\ContextMenu;
use Native\Laravel\Facades\Dock;
use Native\Laravel\Facades\GlobalShortcut;
use Native\Laravel\Facades\Menu;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Window;

class NativeAppServiceProvider
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Artisan::call('migrate --force');

        Menu::create(
            Menu::label('About')->submenu(
                Menu::link('https://github.com/Jacked-PHP/psy-repl', 'Jacked PHP - Psy REPL')
            ),
            Menu::label('View')->submenu(
                Menu::fullscreen()
            ),
        );

        logger()->info('server', [
            'app' => config('app'),
            'native' => config('nativephp'),
        ]);

        MenuBar::create()
            ->label('Psy REPL')
            ->showDockIcon()
            ->icon(storage_path('app/public/logo-bar.png'))
            ->onlyShowContextMenu()
            ->withContextMenu(Menu::make(Menu::quit()));

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

        ContextMenu::register(Menu::make());

        // GlobalShortcut::new()
        //     ->key('CmdOrCtrl+Shift+I')
        //     ->event(ShortcutPressed::class)
        //     ->register();
    }
}
