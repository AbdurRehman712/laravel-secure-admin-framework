<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\app\Models\Admin;

class ShowAdmins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show all admin users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $admins = Admin::with('roles')->get();
        
        $this->info('Admin Users:');
        $this->line('');
        
        $headers = ['ID', 'Name', 'Email', 'Roles'];
        $data = [];
        
        foreach ($admins as $admin) {
            $data[] = [
                $admin->id,
                $admin->name,
                $admin->email,
                $admin->roles->pluck('name')->join(', ')
            ];
        }
        
        $this->table($headers, $data);
        
        return 0;
    }
}
