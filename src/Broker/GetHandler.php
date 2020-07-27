<?php


namespace AMC\Broker;


class GetHandler
{
    private \PDO $db;

    public function __invoke()
    {
        $this->db = new \PDO(
            'pgsql:host=127.0.0.1',
            'postgres',
            'root',
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );

        $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

        $id = $request->getQueryParams()['id'] ?? null;


        $sql = /** @lang PostgreSQL */
            'SELECT * FROM "Broker"."request" WHERE "id" = ?';
        $stmt = $this->db->prepare($sql);
        $executeResult = $stmt->execute([$id]);
        $results = $stmt->fetch();

        header('Content-Type: application/json');
        echo json_encode($results);
    }
}