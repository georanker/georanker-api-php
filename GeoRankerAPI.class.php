<?php

/**
 * Class GeoRanker API.
 * Conect with GeoRanker Api (apidocs.georanker.com)
 * @author Renan Gomes
 */
if (!class_exists("GeoRankerAPI")) {

    class GeoRankerAPI {

        private $email = null;
        private $apikey = null;
        private $session = null;
        private $apiurl = "http://api.georanker.com/v2";
        private $cachefolder = null;
        private $cache = true;
        private $cachetime = 60;

        /**
         * Set the conection mode for the GeoRanker API interaction
         * @param string $conectionmode can be 'curl' or 'fsockopen'
         */
        public function GeoRankerAPI($email, $apikey, $cache = true, $cachetime = 60, $cachefolder = null) {
            $this->email = $email;
            $this->apikey = strtolower($apikey);
            if (!empty($cachefolder)) {
                $this->cachefolder = $cachefolder;
            } else {
                $this->cachefolder = sys_get_temp_dir() . "/";
				//$this->cachefolder = dirname(__FILE__) . "/cache/";
            }
            $this->cachetime = $cachetime;
            $this->cache = $cache;
        }

        public function login() {
            if (empty($this->email) || empty($this->apiurl)) {
                return false;
            }

            $ret = $this->docurl($this->apiurl . '/api/login.json?' . http_build_query(array('email' => $this->email, 'apikey' => $this->apikey)));
            if (!empty($ret['content'])) {
                $responseobj = json_decode(trim($ret['content']));
                if (!empty($responseobj) && !isset($responseobj->debug)) {
                    $this->session = $responseobj->session;
                }
                return $responseobj;
            }
            return false;
        }

        public function accountinfo() {
            if (empty($this->session) && !$this->login()) {
                return false;
            }
            $ret = $this->docurl($this->apiurl . '/account/info.json?' . http_build_query(array('email' => $this->email, 'session' => $this->session)));
            if (!empty($ret['content'])) {
                $responseobj = json_decode(trim($ret['content']));
                return $responseobj;
            }
            return false;
        }

        public function reportnew($type, $keywords, $countries, $is_global, $maxcities, $regions = array(), $url = NULL, $language = NULL, $ignoretypes = null, $is_usealternativetld = FALSE, $is_fillcities = TRUE, $is_formobile = FALSE, $callback = '', $brand = NULL, $is_gmsearchmode = FALSE, $is_localonly = FALSE, $is_carouselfallbackmode = FALSE, $phone = NULL, $fields = NULL) {
            if (empty($this->session) && !$this->login()) {
                return false;
            }

            $post_fields = array();
            $post_fields['type'] = strtolower(trim($type));
            $post_fields['keywords'] = (empty($keywords)) ? array() : $keywords;
            $post_fields['countries'] = (empty($countries)) ? array() : $countries;
            $post_fields['regions'] = (empty($regions)) ? array() : $regions;
            $post_fields['url'] = (empty($url)) ? NULL : trim($url);
            $post_fields['language'] = empty($language) ? NULL : trim($language);
            $post_fields['ignoretypes'] = (empty($ignoretypes)) ? '' : $ignoretypes;
            $post_fields['is_usealternativetld'] = (empty($is_usealternativetld)) ? FALSE : TRUE;
            $post_fields['is_global'] = (empty($is_global)) ? FALSE : TRUE;
            $post_fields['is_fillcities'] = (empty($is_fillcities)) ? FALSE : TRUE;
            $post_fields['is_formobile'] = (empty($is_formobile)) ? FALSE : TRUE;
            $post_fields['maxcities'] = $maxcities;
            $post_fields['callback'] = (empty($callback)) ? NULL : $callback;
            $post_fields['brand'] = (empty($brand)) ? NULL : $brand;
            $post_fields['is_gmsearchmode'] = (empty($is_gmsearchmode)) ? FALSE : TRUE;
            $post_fields['is_localonly'] = (empty($is_localonly)) ? FALSE : TRUE;
            $post_fields['is_carouselfallbackmode'] = (empty($is_carouselfallbackmode)) ? FALSE : TRUE;
            $post_fields['fields'] = (empty($fields)) ? NULL : $fields;
            $post_fields['phone'] = (empty($phone)) ? NULL : $phone;

            $ret = $this->docurl($this->apiurl . '/report/new.json?' . http_build_query(array('email' => $this->email, 'session' => $this->session)), 'POST', 30, array(), json_encode((object) $post_fields));
            //return var_dump($ret);
            if (!empty($ret['content'])) {
                $responseobj = json_decode(trim($ret['content']));
                return $responseobj;
            }
            return false;
        }

        public function countrylist() {
            if (empty($this->session) && !$this->login()) {
                return false;
            }
            $ret = $this->docurl($this->apiurl . '/country/list.json?' . http_build_query(array('email' => $this->email, 'session' => $this->session)));
            if (!empty($ret['content'])) {
                $responseobj = json_decode(trim($ret['content']));
                return $responseobj;
            }
            return false;
        }

        public function reportget($id, $subtype = '') {
            if (empty($id) || (empty($this->session) && !$this->login())) {
                return false;
            }
            if ($subtype === '1stpage') {
                $subtype = 'firstpage';
            }
            if ($subtype === 'advertisers') {
                $subtype = 'advertisers';
            }
            $ret = $this->docurl($this->apiurl . '/report/' . (!empty($subtype) ? $subtype . '/' : '') . ((int) $id) . '.json?' . http_build_query(array('email' => $this->email, 'session' => $this->session)));
            if (!empty($ret['content'])) {
                $responseobj = json_decode(trim($ret['content']));
                return $responseobj;
            }
            return false;
        }


        /**
         * Make a json string be easier to read
         * @param string $json a valid json string
         * @return string The new and improved json string 
         */
        static function beautifyJSON($json) {
            $result = '';
            $pos = 0;
            $strLen = strlen($json);
            $indentStr = '  ';
            $newLine = "\n";
            $prevChar = '';
            $outOfQuotes = true;
            for ($i = 0; $i <= $strLen; $i++) {
                // Grab the next character in the string.
                $char = substr($json, $i, 1);
                // Are we inside a quoted string?
                if ($char == '"' && $prevChar != '\\') {
                    $outOfQuotes = !$outOfQuotes;
                    // If this character is the end of an element, 
                    // output a new line and indent the next line.
                } else if (($char == '}' || $char == ']') && $outOfQuotes) {
                    $result .= $newLine;
                    $pos--;
                    for ($j = 0; $j < $pos; $j++) {
                        $result .= $indentStr;
                    }
                }
                // Add the character to the result string.
                $result .= $char;
                // If the last character was the beginning of an element, 
                // output a new line and indent the next line.
                if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                    $result .= $newLine;
                    if ($char == '{' || $char == '[') {
                        $pos++;
                    }

                    for ($j = 0; $j < $pos; $j++) {
                        $result .= $indentStr;
                    }
                }
                $prevChar = $char;
            }
            return $result;
        }

        private function docurl($url, $method = 'GET', $timeout = 30, $options = array(), $post_fields = '') {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json', 'Content-length: ' . strlen($post_fields)));
            }
            curl_setopt_array($ch, $options);
            $response = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $header_size);
            $content = substr($response, $header_size);
            $theinfo = curl_getinfo($ch);
            //var_dump($theinfo);
            curl_close($ch);
            $outarray = array('headers' => $headers, 'content' => $content, 'info' => $theinfo);
            return $outarray;
        }

    }

}
    