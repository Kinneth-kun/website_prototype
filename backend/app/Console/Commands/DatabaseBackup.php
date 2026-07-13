<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class DatabaseBackup extends Command
{
    protected $signature = 'cms:backup';
    protected $description = 'Create a private database backup and remove expired backup files';

    public function handle(): int
    {
        $connection=config('database.default'); $config=config("database.connections.$connection");
        $name='backups/icm-'.now()->format('Y-m-d-His').($config['driver']==='sqlite'?'.sqlite':'.sql');
        if($config['driver']==='sqlite'){
            $source=$config['database'];
            if(!is_file($source)){ $this->error('SQLite database was not found.'); return self::FAILURE; }
            Storage::disk('local')->put($name,file_get_contents($source));
        }elseif($config['driver']==='mysql'){
            $credentials=tempnam(sys_get_temp_dir(),'icm-db-');
            file_put_contents($credentials,"[client]\nuser={$config['username']}\npassword={$config['password']}\nhost={$config['host']}\nport={$config['port']}\n");
            try{
                $process=new Process([env('MYSQLDUMP_PATH','mysqldump'),"--defaults-extra-file=$credentials",'--single-transaction','--quick','--skip-lock-tables',$config['database']]);
                $process->setTimeout(300); $process->run();
                if(!$process->isSuccessful()){ $this->error('Database backup failed. Confirm MYSQLDUMP_PATH.'); return self::FAILURE; }
                Storage::disk('local')->put($name,$process->getOutput());
            }finally{@unlink($credentials);}
        }else{ $this->error('The configured database driver is not supported.'); return self::FAILURE; }
        $cutoff=now()->subDays((int)env('DB_BACKUP_RETENTION_DAYS',14))->timestamp;
        foreach(Storage::disk('local')->files('backups') as $file) if(Storage::disk('local')->lastModified($file)<$cutoff) Storage::disk('local')->delete($file);
        $this->info("Backup created: $name"); return self::SUCCESS;
    }
}
