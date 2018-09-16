<?php

namespace Kinko\Console\Commands;

use phpseclib\Crypt\RSA;
use Illuminate\Console\Command;

class OAuthKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oauth:keys
                    {--force : Overwrite keys they already exist}
                    {--length=4096 : The length of the private key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the encryption keys for OAuth authentication';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(RSA $rsa)
    {
        $keys = $rsa->createKey($this->input ? (int) $this->option('length') : 4096);

        list($publicKey, $privateKey) = [
            storage_path('oauth-public.key'),
            storage_path('oauth-private.key'),
        ];

        if ((file_exists($publicKey) || file_exists($privateKey)) && ! $this->option('force')) {
            return $this->error('Encryption keys already exist. Use the --force option to overwrite them.');
        }

        file_put_contents($publicKey, array_get($keys, 'publickey'));
        file_put_contents($privateKey, array_get($keys, 'privatekey'));

        $this->info('Encryption keys generated successfully.');
    }
}
