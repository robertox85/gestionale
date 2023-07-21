<?php

namespace App\Models;


use App\Libraries\Database;
use PHPMailer\PHPMailer\PHPMailer;

class Utente extends BaseModel
{
    protected int $id;

    protected ?string $username;
    protected ?string $email;
    protected ?string $password;
    protected ?string $created_at;
    protected ?string $updated_at;
    protected ?int $id_ruolo = 5;


    private string $ruolo;

    private array $errors = [];

    private static function getWhereClause(mixed $filters)
    {
        $where = '';
        $params = [];
        if (isset($filters['filters']) && $filters['filters'] != '') {
            // $filters['filters']['ruoli']
            $where .= ' WHERE ';
            $where .= 'ruoli.id = :ruoli';
            // is an array of ruoli id
            $params[':ruoli'] = $filters['filters']['ruoli'];
        }
        return ['where' => $where, 'params' => $params];
    }

    private static function hashPassword(string $newPassword)
    {
        return password_hash(trim($newPassword), PASSWORD_DEFAULT);
    }

    private static function generateRandomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $password = array();
        // password must contain 8 characters, and at least one number, one lower case letter, one upper case letter
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $password[] = $alphabet[$n];
        }
        return implode($password);
    }

    private static function isValidUsername(mixed $username)
    {
        if (strlen($username) < 3) {
            return false;
        }

        if (strlen($username) > 20) {
            return false;
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return false;
        }

        return true;
    }

    private static function isValidEmail(mixed $email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    public function setRuolo($ruolo)
    {
        $this->ruolo = $ruolo;
    }

    // constructor
    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRuoloId()
    {
        return $this->id_ruolo;
    }

    // setter
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setIdRuolo($id_ruolo): void
    {
        $this->id_ruolo = $id_ruolo;
    }

    public function getIdRuolo()
    {
        return $this->id_ruolo;
    }

    // getCreatedAt
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    // getUpdatedAt
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    // setCreatedAt
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    // setUpdatedAt
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    // getErrors
    public function getErrors()
    {
        return $this->errors;
    }

    //setErrors
    public function setErrors($errors)
    {
        // push to errors array
        $this->errors[] = $errors;
    }

    public function setUsername(mixed $username)
    {
        $this->username = $username;
    }

    public function getRuolo()
    {
        return $this->ruolo;
    }

    public static function getCurrentUser()
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM Utenti WHERE id = :id";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id' => $_SESSION['utente']['id']];
        $user = $db->query($options);
        return new Utente($user[0]->id);
    }

    public function verifyPassword($password)
    {
        if ($this->password !== $password) {
            return false;
        }

        return true;
    }

    public function getRoles(): array
    {
        $roles[] = Ruolo::getById($this->getRuoloId());

        return $roles;

    }

    public function getRuoloObj()
    {
        return Ruolo::getById($this->getRuoloId());
    }

    public function getAnagrafica()
    {
        return Anagrafica::getByUserId($this->getId());
    }

    public static function getPermessiUtente(int $id_utente = null): array
    {
        if ($id_utente === null) {
            $id_utente = $_SESSION['user']['id'] ?? null;
        }

        if ($id_utente === null) {
            return [];
        }

        $user = self::getById($id_utente);
        $role = Ruolo::getById($user->getRuoloId());

        $permessi = $role->getPermessiRuolo();

        return array_map(function ($permission) {
            return $permission->getNome();
        }, $permessi);
    }

    // isValidPassword
    public static function isValidPassword($password)
    {
        if (strlen($password) < 8) {
            return false;
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }


    // isIncomplete

    public function isAnagraficaComplete()
    {
        $anagrafica = $this->getAnagrafica();

        if ($anagrafica === null || $anagrafica === false) {
            return false;
        }

        if ($anagrafica->getTipoUtente() === null) {
            return false;
        }

        if ($anagrafica->getTipoUtente() === 'Azienda') {
            if ($anagrafica->getDenominazione() === null) {
                return false;
            }

            if ($anagrafica->getPartitaIva() === null) {
                return false;
            }
        }

        if ($anagrafica->getTipoUtente() === 'Persona') {
            if ($anagrafica->getNome() === null) {
                return false;
            }

            if ($anagrafica->getCognome() === null) {
                return false;
            }

            if ($anagrafica->getCodiceFiscale() === null) {
                return false;
            }
        }

        if ($anagrafica->getIndirizzo() === null) {
            return false;
        }

        if ($anagrafica->getCap() === null) {
            return false;
        }

        if ($anagrafica->getCitta() === null) {
            return false;
        }

        if ($anagrafica->getProvincia() === null) {
            return false;
        }

        if ($anagrafica->getTelefono() === null) {
            return false;
        }

        if ($anagrafica->getCellulare() === null) {
            return false;
        }


        return true;
    }

    // is LoginDataComplete
    public function isLoginDataComplete()
    {
        if ($this->getEmail() === null) {
            return false;
        }

        if ($this->getPassword() === null) {
            return false;
        }

        return true;
    }


    // getPraticheUtente
    public function getPraticheUtente()
    {

        // get Gruppis and for each gruppo get pratiche
        $gruppi = $this->getGruppi();
        $pratiche = [];

        foreach ($gruppi as $gruppo) {
            $pratiche = array_merge($pratiche, $gruppo->getPratiche());
        }

        // return instances of Pratica
        array_walk($pratiche, function (&$pratica) {
            $pratica = new Pratica($pratica);
        });

        return $pratiche;
    }


    // Questa funzione è usata nei metodi getUtentiTableRows
    public function getPratiche()
    {
        // get Gruppis and for each gruppo get pratiche
        $gruppi = $this->getGruppi();
        $pratiche = [];

        foreach ($gruppi as $gruppo) {
            $praticheIds = $gruppo->getPratiche();
            foreach ($praticheIds as $praticaId) {
                $pratica = new Pratica($praticaId);
                $pratiche[] = $pratica;
            }
        }

        return $pratiche;
    }

    // Questa funzione è usata nei metodi getUtentiTableRows
    public function getGruppi()
    {
        $db = Database::getInstance();
        $sql = "SELECT Gruppi.id, Gruppi.nome FROM Utenti_Gruppi JOIN Gruppi ON Utenti_Gruppi.id_gruppo = Gruppi.id WHERE Utenti_Gruppi.id_utente = :id_utente";
        $options = [];
        $options['query'] = $sql;
        $options['params'] = [':id_utente' => $this->getId()];
        $result = $db->query($options);
        // return instance of Gruppo
        /*return array_map(function ($gruppo) {
            return new Gruppo($gruppo->id);
        }, $result);*/
        return $result;
    }


    public function preparaAggiornamento(array $data)
    {
        // Setta le proprietà dell'utente
        $this->settaProprietaUtente($data);

        // Prepara l'anagrafica
        $anagrafica = $this->preparaAnagrafica($data);
        if ($anagrafica === false) {
            return false;
        }

        // Aggiorna le associazioni gruppo
        $this->aggiornaAssociazioniGruppo($data['gruppi']);

        // Aggiorna l'anagrafica
        $anagrafica->update();

        return true;
    }

    private function settaProprietaUtente(array $data)
    {
        $this->setUsername($data['username']);
        // if password is not empty, set it
        if ($data['password'] != '') {
            $this->setPassword(password_hash(trim($data['password']), PASSWORD_DEFAULT));
        }
        $this->setEmail($data['email']);
        $this->setIdRuolo($data['ruolo']);
    }

    public function validazionePassword(array $data)
    {
        if ($data['password'] != '' && $data['password'] == $data['password_confirm']) {
            if (Utente::isValidPassword($data['password'])) {

                return true;
            } else {
                $this->setErrors('La password deve essere lunga almeno 8 caratteri e deve contenere almeno una lettera maiuscola, una minuscola e un numero');
                return false;
            }
        }

        return true;
    }

    private function preparaAnagrafica(array $data)
    {
        $anagrafica = $this->getAnagrafica();
        if ($anagrafica === false || $anagrafica === null) {
            $this->setErrors('Errore nel caricamento dell\'anagrafica');
            return false;
        }

        // Setta le proprietà dell'anagrafica
        $anagrafica->setProprieta($data);

        return $anagrafica;
    }

    public function aggiornaAssociazioniGruppo(array|null $gruppi, int|null $user_id = null)
    {
        // skip if gruppi is empty, null, or is not an array
        if (empty($gruppi) || !is_array($gruppi)) {
            return;
        }

        // if user_id is null, use $this->getId()
        $user_id = ($user_id === null) ? $this->getId() : $user_id;
        Gruppo::removeRecordFromUtentiGruppiByUtenteId($user_id);
        foreach ($gruppi as $gruppo) {
            Gruppo::addRecordToUtentiGruppi($user_id, $gruppo);
        }
    }


    // getAllUtenti (with Anagrafica, Gruppi and Pratiche)
    public static function getAllUtenti(array $args = [])
    {
        $db = Database::getInstance();
        $sql = "SELECT Utenti.id AS utente_id, Anagrafiche.nome as anagrafica_nome, 
        COUNT(DISTINCT Gruppi.id) as count_gruppi, 
        COUNT(DISTINCT Pratiche.id) as count_pratiche FROM Utenti";
        $sql .= " LEFT JOIN Anagrafiche ON Utenti.id = Anagrafiche.id_utente";
        $sql .= " LEFT JOIN Utenti_Gruppi ON Utenti.id = Utenti_Gruppi.id_utente";
        $sql .= " LEFT JOIN Gruppi ON Utenti_Gruppi.id_gruppo = Gruppi.id";
        $sql .= " LEFT JOIN Pratiche ON Gruppi.id = Pratiche.id_gruppo";

        $options = [];
        // WHERE
        // if $args['filters'] is not empty, add WHERE clause
        // WHERE
        // if $args['filters'] is not empty, add WHERE clause
        if (!empty($args['where'])) {
            $sql .= " WHERE ";
            // id_ruolo is in array of ruoli
            if (isset($args['where']['id_ruolo']) && is_array($args['where']['id_ruolo'])) {
                $ruoli = implode(',', array_map('intval', $args['where']['id_ruolo']['value']));
                $sql .= "Utenti.id_ruolo " . $args['where']['id_ruolo']['operator'] . " ({$ruoli})";
            }
        }

        // if search is set, add to WHERE clause
        if (isset($args['search']) && $args['search'] != '') {
            $sql .= (strpos($sql, 'WHERE') !== false ? " AND " : " WHERE "); // Check if WHERE already exists in SQL
            $sql .= "(Utenti.username LIKE :search OR Utenti.email LIKE :search OR Anagrafiche.nome LIKE :search OR Anagrafiche.denominazione LIKE :search)";
            $options['params'] = [':search' => "%{$args['search']}%"];
        }


        $sql .= " GROUP BY Utenti.id, Anagrafiche.nome, Anagrafiche.denominazione";


        if (!empty($args)) {
            $options['limit'] = $args['limit'];
            $options['offset'] = ($args['currentPage'] - 1) * $args['limit'];
            $options['order_dir'] = $args['order'] ?? 'ASC';
            if ($args['order_by'] == 'id') {
                $options['order_by'] = "Utenti.id";
            } elseif ($args['order_by'] == 'nome') {
                $options['order_by'] = "COALESCE(NULLIF(Anagrafiche.nome, ''), NULLIF(Anagrafiche.denominazione, ''))";
            } elseif ($args['order_by'] == 'pratiche') {
                $options['order_by'] = 'count_pratiche';
            } elseif ($args['order_by'] == 'gruppi') {
                $options['order_by'] = 'count_gruppi';
            } else {
                $options['order_by'] = $args['order_by'];
            }

        }


        $options['query'] = $sql;

        // return instance of the class
        $result = $db->query($options);
        $array = [];
        foreach ($result as $record) {
            $array[] = new Utente($record->utente_id);
        }
        // ora però se ci sono meno utenti dei limiti impostati, devo recuperare gli utenti mancanti
        return $array;
    }

    public function getGruppiCount()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM Utenti_Gruppi WHERE id_utente = :id_utente";
        $options = [
            'query' => $sql,
            'params' => [
                ':id_utente' => $this->getId()
            ]
        ];
        $result = $db->query($options);
        return $result[0]->count;
    }

    public function getPraticheCount()
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM Pratiche WHERE id_gruppo IN (SELECT id_gruppo FROM Utenti_Gruppi WHERE id_utente = :id_utente)";
        $options = [
            'query' => $sql,
            'params' => [
                ':id_utente' => $this->getId()
            ]
        ];
        $result = $db->query($options);
        return $result[0]->count;
    }

    public function validazioneEmail(array $data)
    {
        if ($data['email'] != '') {
            if (Utente::isValidEmail($data['email'])) {
                //$this->setEmail($data['email']);
                return true;
            } else {
                $this->setErrors('L\'email inserita non è valida');
                return false;
            }
        }

        return true;
    }

    public function validazioneUsername(array $data)
    {
        if ($data['username'] != '') {
            if (Utente::isValidUsername($data['username'])) {
                //$this->setUsername($data['username']);
                return true;
            } else {
                $this->setErrors('Lo username deve essere lungo almeno 4 caratteri e può contenere solo lettere, numeri, trattini e underscore');
                return false;
            }
        }

        return true;
    }

    public function usernameIsUnique(mixed $username)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM Utenti WHERE username = :username";
        $options = [
            'query' => $sql,
            'params' => [
                ':username' => $username
            ]
        ];
        $result = $db->query($options);
        if ($result[0]->count > 0) {
            $this->setErrors('Lo username inserito è già in uso');
            return false;
        }
        return true;
    }

    public function emailIsUnique(mixed $email)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM Utenti WHERE email = :email";
        $options = [
            'query' => $sql,
            'params' => [
                ':email' => $email
            ]
        ];
        $result = $db->query($options);
        if ($result[0]->count > 0) {
            $this->setErrors('L\'email inserita è già in uso');
            return false;
        }
        return true;
    }

    // generateNewPasswordAndSendEmail
    public function generateNewPasswordAndSendEmail()
    {
        $newPassword = Utente::generateRandomPassword();
        $this->setPassword(Utente::hashPassword($newPassword));
        $this->update();
        $this->sendEmail($newPassword);
    }

    private function sendEmail(string $newPassword)
    {
        $mail = new PHPMailer(true);
        //$phpmailer = new PHPMailer();
        //$phpmailer->isSMTP();
        //$phpmailer->Host = 'sandbox.smtp.mailtrap.io';
        //$phpmailer->SMTPAuth = true;
        //$phpmailer->Port = 2525;
        //$phpmailer->Username = 'bf74caa722807c';
        //$phpmailer->Password = 'c592e65386edcd';
        //// Destinatari
        //$phpmailer->setFrom('' . $this->getEmail(), '' . $this->getUsername());
        //$phpmailer->addAddress('' . $this->getEmail(), '' . $this->getUsername());
        //// Contenuto
        //$phpmailer->isHTML(true);
        //$phpmailer->Subject = 'L\'utente ' . $this->getUsername() . ' ha richiesto una nuova password';
        //$phpmailer->Body    = 'La nuova password è: ' . $newPassword;
        //$phpmailer->AltBody = 'La nuova password è: ' . $newPassword;
        //$phpmailer->isMail();
        //$phpmailer->send();

        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->Port = 1025;

        $mail->setFrom('from@example.com', 'Mailer');
        $mail->addAddress('to@example.com', 'Receiver');
        $mail->Subject = 'L\'utente ' . $this->getUsername() . ' ha richiesto una nuova password';
        $mail->Body = 'La nuova password è: ' . $newPassword;

        if (!$mail->send()) {
            return false;
        } else {
            return true;
        }
    }
}
