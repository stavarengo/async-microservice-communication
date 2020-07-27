<?php


namespace AMC\Broker;


class PostHandler
{
    private \PDO $db;

    public function __invoke()
    {
        $this->db = new \PDO(
            'pgsql:host=127.0.0.1',
            'postgres',
            'root',
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]
        );

        $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

        $bodyContent = $request->getBody()->getContents();
        $requestBody = $bodyContent ? (object)json_decode($bodyContent) : null;


        $sql = /** @lang PostgreSQL */
            'INSERT INTO "Broker"."request" ("id", "message") VALUES (?, ?);';
        $stmt = $this->db->prepare($sql);
        $id = $this->generateNewId();
        $executeResult = $stmt->execute([$id, $requestBody->message]);

        header('Content-Type: application/json');
        echo json_encode(
            [
                'id' => $id,
            ]
        );
    }


    public static function generateNewId()
    {
        return uniqid() . bin2hex(openssl_random_pseudo_bytes(1));
    }
}