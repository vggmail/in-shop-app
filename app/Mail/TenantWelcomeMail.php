<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant;

class TenantWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $email;
    public $password;
    public $loginUrl;

    public function __construct(Tenant $tenant, $email, $password, $loginUrl)
    {
        $this->tenant = $tenant;
        $this->email = $email;
        $this->password = $password;
        $this->loginUrl = $loginUrl;
    }

    public function build()
    {
        return $this->subject('Welcome to your new shop!')
                    ->html("
                        <h2>Congratulations!</h2>
                        <p>Your shop <strong>{$this->tenant->name}</strong> has been successfully created.</p>
                        <p>You can access your administration panel using the following details:</p>
                        <ul>
                            <li><strong>URL:</strong> <a href='{$this->loginUrl}'>{$this->loginUrl}</a></li>
                            <li><strong>Username:</strong> {$this->email}</li>
                            <li><strong>Password:</strong> {$this->password}</li>
                        </ul>
                        <p>Please change your password after your first login.</p>
                        <br>
                        <p>Regards,<br>Support Team</p>
                    ");
    }
}
