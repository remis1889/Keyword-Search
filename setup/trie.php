<?php
namespace PHPTrie;
class Trie
{
    private $trie = array();
    private $value = null;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function add($string, $value, $overWrite=true)
    {
        if (empty($string)) {
            if (is_null($this->value) || $overWrite) {
                $this->value = $value;
            }
            return;
        }

        foreach ($this->trie as $prefix => $trie) {
            $prefixLength = strlen($prefix);
            $head = substr($string,0,$prefixLength);
            $headLength = strlen($head);

            $equals = true;
            $equalPrefix = "";
            for ($i= 0;$i<$prefixLength;++$i) {
                if ($i >= $headLength) {
                    $equalTrie = new Trie($value);
                    $this->trie[$equalPrefix] = $equalTrie;
                    $equalTrie->trie[substr($prefix,$i)] = $trie;
                    unset($this->trie[$prefix]);

                    return;
                } elseif ($prefix[$i] != $head[$i]) {
                    if ($i > 0) {
                        $equalTrie = new Trie();
                        $this->trie[$equalPrefix] = $equalTrie;
                        $equalTrie->trie[substr($prefix,$i)] = $trie;
                        $equalTrie->trie[substr($string,$i)] = new Trie($value);
                        unset($this->trie[$prefix]);

                        return;
                    }
                    $equals = false;
                    break;
                }

                $equalPrefix .= $head[$i];
            }

            if ($equals) {
                $trie->add(substr($string,$prefixLength),$value,$overWrite);

                return;
            }
        }

        $this->trie[$string] = new Trie($value);
    }

    public function search($string)
    {
        if (empty($string)) {
            return $this->value;
        }

        foreach ($this->trie as $prefix => $trie) {
            $prefixLength = strlen($prefix);
            $head = substr($string,0,$prefixLength);

            if ($head == $prefix) {
                return $trie->search(substr($string,$prefixLength));
            }
        }

        return null;
    }

    public function searchMultiple(array $array, $delimeter=' ')
    {
        $size = count($array);
        $value = null;

        for ($j=0;$j<$size;++$j) {
            $trie = $this;
            $delim = '';
            $key = '';

            for ($i=$j;$i<$size;++$i) {
                $key .= $delim.$array[$i];
                $ret = $trie->searchTrie($key);
                if (is_null($ret)) {
                    break;
                }

                $trie = $ret[1];
                $key = $ret[0];
                $delim = $delimeter;
                if (!is_null($trie->value)) {
                    $value = $trie->value;
                }
            }

            if (!is_null($value)) {
                return $value;
            }
        }

        return null;
    }

    private function searchTrie($string)
    {
        if (empty($string)) {
            return array($string,$this);
        }

        $stringLength = strlen($string);
        foreach ($this->trie as $prefix => $trie) {
            $prefixLength = strlen($prefix);
            if ($prefixLength > $stringLength) {
                $prefix = substr($prefix,0,$stringLength);
                if ($prefix == $string) {
                    return array($string,$this);
                }
            }
            $head = substr($string,0,$prefixLength);

            if ($head == $prefix) {
                return $trie->searchTrie(substr($string,$prefixLength));
            }
        }

        return null;
    }
}
?>
