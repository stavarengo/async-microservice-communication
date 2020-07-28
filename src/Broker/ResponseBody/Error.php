<?php

declare(strict_types=1);


namespace AMC\Broker\ResponseBody;


class Error implements ResponseBodyInterface
{
    protected string $detail;

    /**
     * Error constructor.
     * @param string $detail
     */
    public function __construct(string $detail)
    {
        $this->detail = $detail;
    }

    /**
     * @return string
     */
    public function getDetail(): string
    {
        return $this->detail;
    }

    public function __toString(): string
    {
        $errorAsArray = [
            'error' => true,
            'detail' => $this->detail,
        ];

        return json_encode($errorAsArray);
    }
}