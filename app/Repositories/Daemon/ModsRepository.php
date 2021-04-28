<?php

namespace Pterodactyl\Repositories\Daemon;

use Psr\Http\Message\ResponseInterface;

class ModsRepository extends BaseRepository
{
    /**
     * @param array $data
     * @return ResponseInterface
     */
    public function install(array $data): ResponseInterface
    {
        return $this->getHttpClient()->request('POST', 'server/mods/install', [
            'json' => $data,
        ]);
    }

    /**
     * @param array $data
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function uninstall(array $data): ResponseInterface
    {
        return $this->getHttpClient()->request('POST', 'server/mods/uninstall', [
            'json' => $data,
        ]);
    }
}
