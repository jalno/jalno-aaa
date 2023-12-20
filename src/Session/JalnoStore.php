<?php

namespace Jalno\AAA\Session;

use Illuminate\Session\Store;

class JalnoStore extends Store
{
    /**
     * {@inheritdoc}
     */
    public function isValidId($id)
    {
        return is_string($id);
    }

    public function readFromHandler()
    {
        if ($data = $this->handler->read($this->getId())) {
            $result = null;

            if ($this->serialization === 'json') {
                $result = json_decode($this->prepareForUnserialize($data), true);
            } else {
                $result = @unserialize($this->prepareForUnserialize($data));
            }
            if ($result === null or $result === false) {
                $result = self::unserialize_php($this->prepareForUnserialize($data));
            }
            if ($result === null or $result === false) {
                $result = self::unserialize_phpbinary($this->prepareForUnserialize($data));
            }

            if ($result !== false && is_array($result)) {
                return $result;
            }
        }

        return [];
    }

    private function unserialize_php($session_data): ?array
    {
        $result = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            if (!strstr(substr($session_data, $offset), "|")) {
                return null;
            }
            $pos = strpos($session_data, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session_data, $offset, $num);
            $offset += $num + 1;
            $data = @unserialize(substr($session_data, $offset));
            $result[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $result;
    }

    private function unserialize_phpbinary($session_data)
    {
        $result = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            $num = ord($session_data[$offset]);
            $offset += 1;
            $varname = substr($session_data, $offset, $num);
            $offset += $num;
            $data = unserialize(substr($session_data, $offset));
            $result[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $result;
    }
}
