<?php
class MyAuthenticator implements Nette\Security\IAuthenticator
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
	}

    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;
        $row = $this->database->table('o2_users')
            ->where('username', $username)->fetch();
		if (!$row) {
            throw new Nette\Security\AuthenticationException('User not found.');
        }

        if (!Nette\Security\Passwords::verify($password, $row->password)) {
            throw new Nette\Security\AuthenticationException('Invalid password.');
        }

        return new Nette\Security\Identity($row->user_id, 0, ['username' => $row->username]);
    }
}