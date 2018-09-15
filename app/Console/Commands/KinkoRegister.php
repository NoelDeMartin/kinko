<?php

namespace Kinko\Console\Commands;

use Kinko\Models\User;
use Illuminate\Console\Command;

class KinkoRegister extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kinko:register
                    { --test : Create test user (using default password). }
                    { --password=secret : Default password used for test users. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user in the database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $firstName = $this->ask('First Name');
        $lastName = $this->ask('Last Name');

        while (true) {
            $email = $this->ask('Email');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Invalid email address');
            } elseif (!is_null(User::where('email', $email)->first())) {
                $this->error('Email already in use');
            } else {
                break;
            }
        }

        if ($this->option('test')) {
            $password = $this->option('password');
        } else {
            while (true) {
                $password = $this->secret('Password');
                $passwordConfirmation = $this->secret('Password Confirmation');

                if (strlen($password) < 8) {
                    $this->error('Invalid password (min. 8 characters)');
                } elseif ($password !== $passwordConfirmation) {
                    $this->error('Passwords don\'t match!');
                } else {
                    break;
                }
            }
        }

        $this->line('First Name: ' . $firstName);
        $this->line('Last Name: ' . $lastName);
        $this->line('Email: ' . $email);

        if ($this->confirm('Is the information above correct?')) {
            $user = User::create([
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $email,
                'password'   => bcrypt($password),
                'api_token'  => User::newApiToken(),
            ]);

            $this->info('Created user with id ' . $user->_id);
        } else {
            $this->error('Operation cancelled');
        }
    }
}
