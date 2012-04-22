<?php
/**
 * PHP SDK for weibo.com (using OAuth2)
 * 
 * @author Elmer Zhang <freeboy6716@gmail.com>
 */

/**
 * @ignore
 */
class OAuthException extends Exception {
    // pass
}


/**
 * ����΢�� OAuth ��֤��(OAuth2)
 *
 * @package sae
 * @author Elmer Zhang
 * @version 1.0
 */
class SaeTOAuth {
    /**
     * @ignore
     */
    public $client_id;
    /**
     * @ignore
     */
    public $client_secret;
    /**
     * @ignore
     */
    public $access_token;
    /**
     * @ignore
     */
    public $refresh_token;
    /**
     * Contains the last HTTP status code returned. 
     *
     * @ignore
     */
    public $http_code;
    /**
     * Contains the last API call.
     *
     * @ignore
     */
    public $url;
    /**
     * Set up the API root URL.
     *
     * @ignore
     */
    public $host = "https://api.t.sina.com.cn/";
    /**
     * Set timeout default.
     *
     * @ignore
     */
    public $timeout = 30;
    /**
     * Set connect timeout.
     *
     * @ignore
     */
    public $connecttimeout = 30;
    /**
     * Verify SSL Cert.
     *
     * @ignore
     */
    public $ssl_verifypeer = FALSE;
    /**
     * Respons format.
     *
     * @ignore
     */
    public $format = 'json';
    /**
     * Decode returned json data.
     *
     * @ignore
     */
    public $decode_json = TRUE;
    /**
     * Contains the last HTTP headers returned.
     *
     * @ignore
     */
    public $http_info;
    /**
     * Set the useragnet.
     *
     * @ignore
     */
    public $useragent = 'Sae T OAuth2 v0.1';

    /**
     * print the debug info
     *
     * @ignore
     */
    public $debug = FALSE;

    /**
     * boundary of multipart
     * @ignore
     */
    public static $boundary = '';

    /**
     * Set API URLS
     */
    /**
     * @ignore
     */
    function accessTokenURL()  { return 'https://api.t.sina.com.cn/oauth2/access_token'; }
    /**
     * @ignore
     */
    function authorizeURL()    { return 'https://api.t.sina.com.cn/oauth2/authorize'; }

    /**
     * construct WeiboOAuth object
     */
    function __construct($client_id, $client_secret, $access_token = NULL, $refresh_token = NULL) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
    }

    /**
     * Get the authorize URL
     *
     * @return string
     */
    function getAuthorizeURL( $url, $response_type = 'code' ) {
        $params = array();
        $params['client_id'] = $this->client_id;
        $params['redirect_uri'] = $url;
        $params['response_type'] = $response_type;
        return $this->authorizeURL() . "?" . http_build_query($params);
    }

    /**
     * Exchange the request token and secret for an access token and
     * secret, to sign API calls.
     *
     * @return array array("oauth_token" => the access token,
     *                "oauth_token_secret" => the access secret)
     */
    function getAccessToken( $type = 'code', $keys ) {
        $params = array();
        $params['client_id'] = $this->client_id;
        $params['client_secret'] = $this->client_secret;
        if ( $type === 'token' ) {
            $params['grant_type'] = 'refresh_token';
            $params['refresh_token'] = $keys['refresh_token'];
        } elseif ( $type === 'code' ) {
            $params['grant_type'] = 'authorization_code';
            $params['code'] = $keys['code'];
            $params['redirect_uri'] = $keys['redirect_uri'];
        } elseif ( $type === 'password' ) {
            $params['grant_type'] = 'password';
            $params['username'] = $keys['username'];
            $params['password'] = $keys['password'];
        } else {
            throw new OAuthException("wrong auth type");
        }

        $response = $this->oAuthRequest($this->accessTokenURL(), 'POST', $params);
        $token = json_decode($response, true);
        if ( is_array($token) && !isset($token['error']) ) {
            $this->access_token = $token['access_token'];
            $this->refresh_token = $token['refresh_token'];
        } else {
            throw new OAuthException("get access token failed." . $token['error']);
        }
        return $token;
    }

    /**
     * ���� signed_request
     *
     * @param string $signed_request Ӧ�ÿ���ڼ���iframeʱ��ͨ����Canvas URL post�Ĳ���signed_request
     *
     * @return array
     */
    function parseSignedRequest($signed_request) {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
        $sig = self::base64decode($encoded_sig) ;
        $data = json_decode(self::base64decode($payload), true);
        if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') return '-1';
        $expected_sig = hash_hmac('sha256', $payload, $this->client_secret, true);
        return ($sig !== $expected_sig)? '-2':$data;
    }

    /**
     * @ignore
     */
    function base64decode($str) {
        return base64_decode(strtr($str.str_repeat('=',(4 - strlen($str) % 4)), '-_', '+/'));
    }

    /**
     * ��ȡjssdk��Ȩ��Ϣ�����ں�jssdk��ͬ����¼
     *
     * @return array �ɹ�����array('access_token'=>'value', 'refresh_token'=>'value'); ʧ�ܷ���false
     */
    function getTokenFromJSSDK() {
        $key = "weibojs_" . $this->client_id;
        if ( isset($_COOKIE[$key]) && $cookie = $_COOKIE[$key] ) {
            parse_str($cookie, $token);
            if ( isset($token['access_token']) && isset($token['refresh_token']) ) {
                $this->access_token = $token['access_token'];
                $this->refresh_token = $token['refresh_token'];
                return $token;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * �������ж�ȡaccess_token��refresh_token
     * �����ڴ�Session��Cookie�ж�ȡtoken����ͨ��Session/Cookie���Ƿ����token�жϵ�¼״̬��
     *
     * @param array $arr ����access_token��secret_token������
     * @return array �ɹ�����array('access_token'=>'value', 'refresh_token'=>'value'); ʧ�ܷ���false
     */
    function getTokenFromArray( $arr ) {
        if (isset($arr['access_token']) && $arr['access_token']) {
            $token = array();
            $this->access_token = $token['access_token'] = $arr['access_token'];
            if (isset($arr['refresh_token']) && $arr['refresh_token']) {
                $this->refresh_token = $token['refresh_token'] = $arr['refresh_token'];
            }

            return $token;
        } else {
            return false;
        }
    }

    /**
     * GET wrappwer for oAuthRequest.
     *
     * @return mixed
     */
    function get($url, $parameters = array()) {
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * POST wreapper for oAuthRequest.
     *
     * @return mixed
     */
    function post($url, $parameters = array() , $multi = false) {
        $response = $this->oAuthRequest($url, 'POST', $parameters , $multi );
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * DELTE wrapper for oAuthReqeust.
     *
     * @return mixed
     */
    function delete($url, $parameters = array()) {
        $response = $this->oAuthRequest($url, 'DELETE', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * Format and sign an OAuth / API request
     *
     * @return string
     */
    function oAuthRequest($url, $method, $parameters , $multi = false) {

        if (strrpos($url, 'https://') !== 0 && strrpos($url, 'https://') !== 0) {
            $url = "{$this->host}{$url}.{$this->format}";
        }

        switch ($method) {
        case 'GET':
            $url = $url . '?' . http_build_query($parameters);
            return $this->http($url, 'GET');
        default:
            $headers = array();
            if (!$multi && (is_array($parameters) || is_object($parameters)) ) {
                $body = http_build_query($parameters);
            } else {
                $body = self::build_http_query_multi($parameters);
                $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
            }
            return $this->http($url, $method, $body, $headers);
        }
    }

    /**
     * Make an HTTP request
     *
     * @return string API results
     */
    function http($url, $method, $postfields = NULL, $headers = array()) {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
        case 'POST':
            curl_setopt($ci, CURLOPT_POST, TRUE);
            if (!empty($postfields)) {
                curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                $this->postdata = $postfields;
            }
            break;
        case 'DELETE':
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if (!empty($postfields)) {
                $url = "{$url}?{$postfields}";
            }
        }

        if ( isset($this->access_token) && $this->access_token )
            $headers[] = "Authorization: OAuth2 ".$this->access_token;

        $headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
        curl_setopt($ci, CURLOPT_URL, $url );
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;

        if ($this->debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo '=====info====='."\r\n";
            print_r( curl_getinfo($ci) );

            echo '=====$response====='."\r\n";
            print_r( $response );
        }
        curl_close ($ci);
        return $response;
    }

    /**
     * Get the header info to store.
     *
     * @return int
     */
    function getHeader($ch, $header) {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }

    public static function build_http_query_multi($params) {
        if (!$params) return '';

        uksort($params, 'strcmp');

        $pairs = array();

        self::$boundary = $boundary = uniqid('------------------');
        $MPboundary = '--'.$boundary;
        $endMPboundary = $MPboundary. '--';
        $multipartbody = '';

        foreach ($params as $parameter => $value) {

            if( in_array($parameter, array('pic', 'image')) && $value{0} == '@' ) {
                $url = ltrim( $value , '@' );
                $content = file_get_contents( $url );
                $array = explode( '?' , basename( $url ) );
                $filename = $array[0];

                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
                $multipartbody .= "Content-Type: image/unknown\r\n\r\n";
                $multipartbody .= $content. "\r\n";
            } else {
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
                $multipartbody .= $value."\r\n";
            }

        }

        $multipartbody .= $endMPboundary;
        return $multipartbody;
    }
}


/**
 * ����΢��������
 *
 * ʹ��ǰ��Ҫ���ֹ�����saet2.ex.class.php <br />
 * Demo����http://apidoc.sinaapp.com/demo/saetdemo.zip <br />
 * Demoʹ��˵����
 *  - ����,Ȼ���ѹ,�޸�config.php�е�key
 *  - ��index.php,��13�����һ��url�ĳ�����վ��Ӧ��callback.php��url
 *  - �ϴ���SAEƽ̨����
 *
 * @package sae
 * @author Easy Chen, Elmer Zhang
 * @version 1.0
 */
class SaeTClient
{
    /**
     * ���캯��
     * 
     * @access public
     * @param mixed $akey ΢������ƽ̨Ӧ��APP KEY
     * @param mixed $skey ΢������ƽ̨Ӧ��APP SECRET
     * @param mixed $access_token OAuth��֤���ص�token
     * @param mixed $refresh_token OAuth��֤���ص�token secret
     * @return void
     */
    function __construct( $akey , $skey , $access_token , $refresh_token = NULL)
    {
        $this->oauth = new SaeTOAuth( $akey , $skey , $access_token , $refresh_token );
    }

    /**
     * ��ȡ���µĹ���΢����Ϣ
     *
     * �������µ�20������΢�������ؽ������ȫʵʱ����Ỻ��60��
     * <br />��ӦAPI��statuses/public_timeline
     * 
     * @access public
     * @param int $count ÿ�η��صļ�¼����ȱʡֵ20�����ֵ200����ѡ��
     * @param int $base_app �Ƿ���ڵ�ǰӦ������ȡ���ݡ�1Ϊ���Ʊ�Ӧ��΢����0Ϊ�������ơ�Ĭ��Ϊ0����ѡ��
     * @return array
     */
    function public_timeline( $count = 20, $base_app = 0 )
    {
        $params = array();
        $params['count'] = intval($count);
        $params['base_app'] = intval($base_app);
        return $this->oauth->get('https://api.t.sina.com.cn/statuses/public_timeline.json', $params);
    }

    /**
     * ��ȡ��ǰ��¼�û���������ע�û�������΢����Ϣ��
     *
     * ��ȡ��ǰ��¼�û���������ע�û�������΢����Ϣ�����û���¼ http://t.sina.com.cn ���ڡ��ҵ���ҳ���п�����������ͬ��ͬhome_timeline()
     * <br />��ӦAPI��statuses/home_timeline
     * 
     * @access public
     * @param int $page ָ�����ؽ����ҳ�롣���ݵ�ǰ��¼�û�����ע���û�������Щ����ע�û������΢��������ҳ��������ܲ鿴���ܼ�¼����������ͬ��ͨ������ܲ鿴1000�����ҡ�Ĭ��ֵ1����ѡ��
     * @param int $count ÿ�η��صļ�¼����ȱʡֵ20�����ֵ200����ѡ��
     * @param int64 $since_id ��ָ���˲�������ֻ����ID��since_id���΢����Ϣ������since_id����ʱ�����΢����Ϣ������ѡ��
     * @param int64 $max_id ��ָ���˲������򷵻�IDС�ڻ����max_id��΢����Ϣ����ѡ��
     * @param int $base_app �Ƿ���ڵ�ǰӦ������ȡ���ݡ�1Ϊ���Ʊ�Ӧ��΢����0Ϊ�������ơ�Ĭ��Ϊ0����ѡ��
     * @param int $feature ΢�����ͣ�0ȫ����1ԭ����2ͼƬ��3��Ƶ��4����. ����ָ�����͵�΢����Ϣ���ݡ�תΪΪ0����ѡ��
     * @return array
     */
    function friends_timeline( $page = 1, $count = 20, $since_id = NULL, $max_id = NULL, $base_app = 0, $feature = 0 )
    {
        return $this->home_timeline( $page, $count, $since_id, $max_id, $base_app, $feature );
    }

    /**
     * ��ȡ��ǰ��¼�û���������ע�û�������΢����Ϣ��
     *
     * ��ȡ��ǰ��¼�û���������ע�û�������΢����Ϣ�����û���¼ http://t.sina.com.cn ���ڡ��ҵ���ҳ���п�����������ͬ��ͬfriends_timeline()
     * <br />��ӦAPI��statuses/home_timeline
     * 
     * @access public
     * @param int $page ָ�����ؽ����ҳ�롣���ݵ�ǰ��¼�û�����ע���û�������Щ����ע�û������΢��������ҳ��������ܲ鿴���ܼ�¼����������ͬ��ͨ������ܲ鿴1000�����ҡ�Ĭ��ֵ1����ѡ��
     * @param int $count ÿ�η��صļ�¼����ȱʡֵ20�����ֵ200����ѡ��
     * @param int64 $since_id ��ָ���˲�������ֻ����ID��since_id���΢����Ϣ������since_id����ʱ�����΢����Ϣ������ѡ��
     * @param int64 $max_id ��ָ���˲������򷵻�IDС�ڻ����max_id��΢����Ϣ����ѡ��
     * @param int $base_app �Ƿ���ڵ�ǰӦ������ȡ���ݡ�1Ϊ���Ʊ�Ӧ��΢����0Ϊ�������ơ�Ĭ��Ϊ0����ѡ��
     * @param int $feature ΢�����ͣ�0ȫ����1ԭ����2ͼƬ��3��Ƶ��4����. ����ָ�����͵�΢����Ϣ���ݡ�תΪΪ0����ѡ��
     * @return array
     */
    function home_timeline( $page = 1, $count = 20, $since_id = NULL, $max_id = NULL, $base_app = 0, $feature = 0 )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }
        $params['base_app'] = intval($base_app);
        $params['feature'] = intval($feature);

        return $this->request_with_pager('https://api.t.sina.com.cn/statuses/home_timeline.json', $page, $count, $params );
    }

    /**
     * ��ȡ@��ǰ�û���΢���б�
     *
     * ��������n���ᵽ��¼�û���΢����Ϣ��������@username��΢����Ϣ��
     * <br />��ӦAPI��statuses/mentions
     * 
     * @access public
     * @param int $page ���ؽ����ҳ��š�
     * @param int $count ÿ�η��ص�����¼������ҳ���С����������200��Ĭ��Ϊ20��
     * @param int64 $since_id ��ָ���˲�������ֻ����ID��since_id���΢����Ϣ������since_id����ʱ�����΢����Ϣ������ѡ��
     * @param int64 $max_id ��ָ���˲������򷵻�IDС�ڻ����max_id���ᵽ��ǰ��¼�û�΢����Ϣ����ѡ��
     * @return array
     */
    function mentions( $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'https://api.t.sina.com.cn/statuses/mentions.json' , $page , $count, $params );
    }

    /**
     * ����΢��
     *
     * ����һ��΢����Ϣ�����������POST��ʽ�ύ��Ϊ��ֹ�ظ�����������Ϣ�뵱ǰ������Ϣһ���������ᱻ���ԡ�<br />
     * ע�⣺lat��long���������ʹ�ã����ڱ�Ƿ���΢����Ϣʱ���ڵĵ���λ�ã�ֻ���û�������geo_enabled=trueʱ�����λ����Ϣ����Ч��
     * <br />��ӦAPI��statuses/update
     * 
     * @access public
     * @param string $status Ҫ���µ�΢����Ϣ����Ϣ���ݲ�����140������,Ϊ�շ���400����
     * @param int64 $reply_id @ ��Ҫ�ظ���΢����ϢID, �������ֻ����΢�������� @username ��ͷ�������塣�������Ƴ�������ѡ
     * @param float $lat γ�ȣ�����ǰ΢�����ڵĵ���λ�ã���Ч��Χ -90.0��+90.0, +��ʾ��γ����ѡ��
     * @param float $long ���ȡ���Ч��Χ-180.0��+180.0, +��ʾ��������ѡ��
     * @param mixed $annotations ��ѡ������Ԫ���ݣ���Ҫ��Ϊ�˷��������Ӧ�ü�¼һЩ�ʺ����Լ�ʹ�õ���Ϣ��ÿ��΢�����԰���һ�����߶��Ԫ���ݡ�����json�ִ�����ʽ�ύ���ִ����Ȳ�����512���ַ����������鷽ʽ��Ҫ��json_encode���ִ����Ȳ�����512���ַ����������ݿ����Զ������磺'[{"type2":123},{"a":"b","c":"d"}]'��array(array("type2"=>123), array("a"=>"b", "c"=>"d"))��
     * @return array
     */
    function update( $status, $reply_id = NULL, $lat = NULL, $long = NULL, $annotations = NULL )
    {
        //  https://api.t.sina.com.cn/statuses/update.json
        $params = array();
        $params['status'] = $status;
        if ($reply_id) {
            $this->id_format($reply_id);
            $params['in_reply_to_status_id'] = $reply_id;
        }
        if ($lat) {
            $params['lat'] = floatval($lat);
        }
        if ($long) {
            $params['long'] = floatval($long);
        }
        if (is_string($annotations)) {
            $params['annotations'] = $annotations;
        } elseif (is_array($annotations)) {
            $params['annotations'] = json_encode($annotations);
        }
        
        return $this->oauth->post( 'https://api.t.sina.com.cn/statuses/update.json' , $params );
    }

    /**
     * ����ͼƬ΢��
     *
     * �ϴ�ͼƬ������΢����Ϣ�����������POST��ʽ�ύ��Ϊ��ֹ�ظ�����������Ϣ�뵱ǰ������Ϣһ���������ᱻ���ԡ�Ŀǰ�ϴ�ͼƬ��С����Ϊ<5M��<br />
     * ע�⣺lat��long���������ʹ�ã����ڱ�Ƿ���΢����Ϣʱ���ڵĵ���λ�ã�ֻ���û�������geo_enabled=trueʱ�����λ����Ϣ����Ч��
     * <br />��ӦAPI��statuses/upload
     * 
     * @access public
     * @param string $status Ҫ���µ�΢����Ϣ����Ϣ���ݲ�����140������,Ϊ�շ���400����
     * @param string $pic_path Ҫ������ͼƬ·��,֧��url��[ֻ֧��png/jpg/gif���ָ�ʽ,���Ӹ�ʽ���޸�get_image_mime����]
     * @param float $lat γ�ȣ�����ǰ΢�����ڵĵ���λ�ã���Ч��Χ -90.0��+90.0, +��ʾ��γ����ѡ��
     * @param float $long ��ѡ���������ȡ���Ч��Χ-180.0��+180.0, +��ʾ��������ѡ��
     * @return array
     */
    function upload( $status , $pic_path, $lat = NULL, $long = NULL )
    {
        //  https://api.t.sina.com.cn/statuses/update.json
        $params = array();
        $params['status'] = $status;
        $tempimg = tempnam(NULL, 'SAETIMG');
        $content = file_get_contents($pic_path);
        file_put_contents($tempimg, $content);
        $params['pic'] = '@'.$tempimg;
        if ($lat) {
            $params['lat'] = floatval($lat);
        }
        if ($long) {
            $params['long'] = floatval($long);
        }

        return $this->oauth->post( 'https://api.t.sina.com.cn/statuses/upload.json' , $params , true );
    }

    /**
     * ����ID��ȡ����΢����Ϣ����
     *
     * ��ȡ����ID��΢����Ϣ��������Ϣ��ͬʱ���ء�
     * <br />��ӦAPI��statuses/show
     * 
     * @access public
     * @param int64 $sid Ҫ��ȡ�ѷ����΢��ID,��ID�����ڷ��ؿ�
     * @return array
     */
    function show_status( $sid )
    {
        $this->id_format($sid);
        return $this->oauth->get( 'https://api.t.sina.com.cn/statuses/show/' . $sid . '.json' );
    }

    /**
     * ɾ��һ��΢��
     *
     * ɾ��΢����ע�⣺ֻ��ɾ���Լ���������Ϣ��
     * <br />��ӦAPI��statuses/destroy
     * 
     * @access public
     * @param int64 $sid Ҫɾ����΢��ID
     * @return array
     */
    function delete( $sid )
    {
        $this->id_format($sid);
        return $this->destroy( $sid );
    }

    /**
     * ɾ��һ��΢��
     *
     * ɾ��΢����ע�⣺ֻ��ɾ���Լ���������Ϣ��
     * <br />��ӦAPI��statuses/destroy
     * 
     * @access public
     * @param int64 $sid Ҫɾ����΢��ID
     * @return array
     */
    function destroy( $sid )
    {
        $this->id_format($sid);
        return $this->oauth->post( 'https://api.t.sina.com.cn/statuses/destroy/' . $sid . '.json' );
    }

    /**
     * �����û�UID���ǳƻ�ȡ�û�����
     *
     * ���û�UID���ǳƷ����û����ϣ�ͬʱҲ�������û������·�����΢����
     * <br />��ӦAPI��users/show
     * 
     * @access public
     * @param mixed $uid_or_name �û�UID��΢���ǳơ�
     * @return array
     */
    function show_user( $uid_or_name )
    {
        return $this->request_with_uid( 'https://api.t.sina.com.cn/users/show.json' ,  $uid_or_name );
    }

    /**
     * ��ȡ�û���ע�����б�����һ��΢����Ϣ
     *
     * ��ȡ�û���ע�б�ÿ����ע�û�����һ��΢�������ؽ������עʱ�䵹�����У����¹�ע���û�����ǰ�档
     * <br />��ӦAPI��statuses/friends
     * 
     * @access public
     * @param int $cursor ��ҳֻ�ܰ���100����ע�б�Ϊ�˻�ȡ������cursorĬ�ϴ�-1��ʼ��ͨ�����ӻ����cursor����ȡ����Ĺ�ע�б���ѡ��
     * @param int $count ÿ�η��ص�����¼������ҳ���С����������200,Ĭ�Ϸ���20����ѡ��
     * @param mixed $uid_or_name �û�UID��΢���ǳơ����ṩʱĬ�Ϸ��ص�ǰ�û��Ĺ�ע�б���ѡ��
     * @return array
     */
    function friends( $cursor = NULL , $count = 20 , $uid_or_name = NULL )
    {
        return $this->request_with_uid( 'https://api.t.sina.com.cn/statuses/friends.json' ,  $uid_or_name , NULL , $count , $cursor );
    }

    /**
     * ��ȡ�û���˿�б�ÿ����˿�û�����һ��΢��
     *
     * �����û��ķ�˿�б������ط�˿������΢��������˿�Ĺ�עʱ�䵹�򷵻أ�ÿ�η���100����ע��Ŀǰ�ӿ����ֻ����5000����˿��
     * <br />��ӦAPI��statuses/followers
     * 
     * @access public
     * @param int $cursor ��ҳֻ�ܰ���100����˿�б�Ϊ�˻�ȡ������cursorĬ�ϴ�-1��ʼ��ͨ�����ӻ����cursor����ȡ����ģ����û����һҳ����next_cursor����0����ѡ��
     * @param int $count ÿ�η��ص�����¼������ҳ���С����������200,Ĭ�Ϸ���20����ѡ��
     * @param mixed $uid_or_name Ҫ��ȡ��˿�� UID��΢���ǳơ����ṩʱĬ�Ϸ��ص�ǰ�û��Ĺ�ע�б���ѡ��
     * @return array
     */
    function followers( $cursor = NULL , $count = NULL , $uid_or_name = NULL )
    {
        return $this->request_with_uid( 'https://api.t.sina.com.cn/statuses/followers.json' ,  $uid_or_name , NULL , $count , $cursor );
    }

    /**
     * ��עһ���û�
     *
     * ��עһ���û����ɹ��򷵻ع�ע�˵����ϣ�Ŀǰ������ע2000�ˣ�ʧ���򷵻�һ���ַ�����˵��������Ѿ���ע�˴��ˣ��򷵻�http 403��״̬����ע�����ڵ�ID������400��
     * <br />��ӦAPI��friendships/create
     * 
     * @access public
     * @param mixed $uid_or_name Ҫ��ע���û�UID��΢���ǳ�
     * @return array
     */
    function follow( $uid_or_name )
    {
        return $this->request_with_uid( 'https://api.t.sina.com.cn/friendships/create.json' ,  $uid_or_name ,  NULL , NULL , NULL , true  );
    }

    /**
     * ȡ����עĳ�û�
     *
     * ȡ����עĳ�û����ɹ��򷵻ر�ȡ����ע�˵����ϣ�ʧ���򷵻�һ���ַ�����˵����
     * <br />��ӦAPI��friendships/destroy
     * 
     * @access public
     * @param mixed $uid_or_name Ҫȡ����ע���û�UID��΢���ǳ�
     * @return array
     */
    function unfollow( $uid_or_name )
    {
        return $this->request_with_uid( 'https://api.t.sina.com.cn/friendships/destroy.json' ,  $uid_or_name ,  NULL , NULL , NULL , true);
    }

    /**
     * ����΢��ID���û�ID���ص�����΢��ҳ���ַ
     *
     * ���ص���΢����Web��ַ������ͨ����url��ת��΢����Ӧ��Web��ҳ��
     * <br />��ӦAPI��user/statuses/id
     * 
     * @access public
     * @param int64 $sid ΢����Ϣ��ID
     * @param int64 $uid ΢����Ϣ�ķ�����ID����ѡ��
     * @return array
     */
    function get_status_url( $sid, $uid = NULL )
    {
        $this->id_format($sid);
        if ( !$uid ) {
            $status_info = $this->show_status($sid);
            if ($status_info) {
                $uid = $status_info['user']['id'];
                $this->id_format($uid);
            } else {
                return false;
            }
        }
        
        return "https://api.t.sina.com.cn/$uid/statuses/$sid";
    }

    /**
     * ���µ�ǰ��¼�û�����ע��ĳ�����ѵı�ע��Ϣ
     *
     * ֻ���޸ĵ�ǰ��¼�û�����ע���û��ı�ע��Ϣ�����򽫸���400����
     * <br />��ӦAPI��friends/update_remark
     * 
     * @access public
     * @param int64 $uid ��Ҫ�޸ı�ע��Ϣ���û�ID��
     * @param string $remark ��ע��Ϣ��
     * @return array
     */
    function update_remark( $uid, $remark )
    {
        $this->id_format($uid);

        $params = array();
        $params['user_id'] = $uid;
        $params['remark'] = $remark;

        return $this->oauth->post( 'https://api.t.sina.com.cn/user/friends/update_remark.json' , $params );
    }

    /**
     * ��ȡϵͳ�Ƽ��û�
     *
     * ����ϵͳ�Ƽ����û��б�
     * <br />��ӦAPI��users/hot
     * 
     * @access public
     * @param string $category ���࣬��ѡ����������ĳһ�����Ƽ��û���Ĭ��Ϊ default������������·����У����ؿ��б�<br />
     *  - default:������ע
     *  - ent:Ӱ������
     *  - hk_famous:��̨����
     *  - model:ģ��
     *  - cooking:��ʳ&����
     *  - sport:��������
     *  - finance:�̽�����
     *  - tech:IT������
     *  - singer:����
     *  - writer������
     *  - moderator:������
     *  - medium:ý���ܱ�
     *  - stockplayer:���ɸ���
     * @return array
     */
    function hot_users( $category = "default" )
    {
        $params = array();
        $params['category'] = $category;

        return $this->oauth->get( 'https://api.t.sina.com.cn/users/hot.json' , $params );
    }

    /**
     * ��ȡ�����б�
     *
     * ��������΢���ٷ����б��顢ħ������������Ϣ����������������͡�������࣬�Ƿ����ŵȡ�
     * <br />��ӦAPI��emotions
     * 
     * @access public
     * @param string $type �������"face":��ͨ���飬"ani"��ħ�����飬"cartoon"���������顣Ĭ��Ϊ"face"����ѡ��
     * @param string $language �������"cnname"���壬"twname"���塣Ĭ��Ϊ"cnname"����ѡ
     * @return array
     */
    function emotions( $type = "face", $language = "cnname" )
    {
        $params = array();
        $params['type'] = $type;
        $params['language'] = $language;

        return $this->oauth->get( 'https://api.t.sina.com.cn/emotions.json' , $params );
    }

    /**
     * δ����Ϣ������
     *
     * ����ǰ��¼�û���ĳ������Ϣ��δ����Ϊ0����������ļ�������У�1. ��������2. @me����3. ˽������4. ��ע��
     * <br />��ӦAPI��statuses/reset_count
     * 
     * @access public
     * @param int $type ��Ҫ����ļ������ֵΪ�����ĸ�֮һ��1. ��������2. @me����3. ˽������4. ��ע��
     * @return array
     */
    function reset_count( $type )
    {
        $params = array();
        $params['type'] = intval($type);

        return $this->oauth->get( 'https://api.t.sina.com.cn/statuses/reset_count.json' , $params );
    }

    /**
     * ���������û���ϵ����ϸ���
     *
     * ����û��ѵ�¼���˽ӿڽ��Զ�ʹ�õ�ǰ�û�ID��Ϊsource_id�����ǿ�ǿ��ָ��source_id����ѯ��ϵ<br />
     * ���Դ�û���Ŀ���û������ڣ�������http��400����
     * <br />��ӦAPI��friendships/show
     * 
     * @access public
     * @param mixed $target Ҫ��ѯ���û�UID��΢���ǳ�
     * @param mixed $source Դ�û�UID��Դ΢���ǳƣ���ѡ
     * @return array
     */
    function is_followed( $target, $source = NULL )
    {
        $this->id_format($target);
        $params = array();
        if( is_numeric( $target ) ) $params['target_id'] = $target;
        else $params['target_screen_name'] = $target;

        if ( $source != NULL ) {
            $this->id_format($source);
            if( is_numeric( $source ) ) $params['source_id'] = $source;
            else $params['source_screen_name'] = $source;
        }

        return $this->oauth->get( 'https://api.t.sina.com.cn/friendships/show.json' , $params );
    }

    /**
     * ��ȡ�û�������΢����Ϣ�б�
     *
     * �����û��ķ��������n����Ϣ�����û�΢��ҳ�淵��������һ�µġ��˽ӿ�Ҳ�������������û������·���΢����
     * <br />��ӦAPI��statuses/user_timeline
     * 
     * @access public
     * @param int $page ҳ��
     * @param int $count ÿ�η��ص�����¼������෵��200����Ĭ��20��
     * @param mixed $uid_or_name ָ���û�UID��΢���ǳ�
     * @param int64 $since_id ��ָ���˲�������ֻ����ID��since_id���΢����Ϣ������since_id����ʱ�����΢����Ϣ������ѡ��
     * @param int64 $max_id ��ָ���˲������򷵻�IDС�ڻ����max_id���ᵽ��ǰ��¼�û�΢����Ϣ����ѡ��
     * @return array
     */
    function user_timeline( $page = 1 , $count = 20 , $uid_or_name = NULL , $since_id = NULL, $max_id = NULL)
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_uid( 'https://api.t.sina.com.cn/statuses/user_timeline.json' ,  $uid_or_name , $page , $count , NULL , true, $params );
    }

    /**
     * ��ȡ��ǰ�û�����˽���б�
     *
     * �����û�������n��˽�ţ������������ߺͽ����ߵ���ϸ���ϡ�
     * <br />��ӦAPI��direct_messages
     * 
     * @access public
     * @param int $page ҳ��
     * @param int $count ÿ�η��ص�����¼������෵��200����Ĭ��20��
     * @param int64 $since_id ����ID����ֵsince_id�󣨱�since_idʱ����ģ���˽�š���ѡ��
     * @param int64 $max_id ����ID������max_id(ʱ�䲻����max_id)��˽�š���ѡ��
     * @return array
     */
    function list_dm( $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'https://api.t.sina.com.cn/direct_messages.json' , $page , $count, $params );
    }

    /**
     * ��ȡ��ǰ�û����͵�����˽���б�
     *
     * ���ص�¼�û��ѷ�������20��˽�š����������ߺͽ����ߵ���ϸ���ϡ�
     * <br />��ӦAPI��direct_messages/sent
     * 
     * @access public
     * @param int $page ҳ��
     * @param int $count ÿ�η��ص�����¼������෵��200����Ĭ��20��
     * @param int64 $since_id ����ID����ֵsince_id�󣨱�since_idʱ����ģ���˽�š���ѡ��
     * @param int64 $max_id ����ID������max_id(ʱ�䲻����max_id)��˽�š���ѡ��
     * @return array
     */
    function list_dm_sent( $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'https://api.t.sina.com.cn/direct_messages/sent.json' , $page , $count, $params );
    }

    /**
     * ����˽��
     *
     * ����һ��˽�š��ɹ������������ķ�����Ϣ��
     * <br />��ӦAPI��direct_messages/new
     * 
     * @access public
     * @param mixed $uid_or_name UID��΢���ǳ�
     * @param string $text Ҫ��������Ϣ���ݣ��ı���С����С��300�����֡�
     * @return array
     */
    function send_dm( $uid_or_name , $text )
    {
        $this->id_format($uid_or_name);
        $params = array();
        $params['text'] = $text;
        $params['id'] = $uid_or_name;

        return $this->oauth->post( 'https://api.t.sina.com.cn/direct_messages/new.json' , $params  );
    }

    /**
     * ɾ��һ��˽��
     *
     * ��IDɾ��˽�š������û�����Ϊ˽�ŵĽ����ˡ�
     * <br />��ӦAPI��direct_messages/destroy
     * 
     * @access public
     * @param int64 $did Ҫɾ����˽������ID
     * @return array
     */
    function delete_dm( $did )
    {
        $this->id_format($did);
        return $this->oauth->post( 'https://api.t.sina.com.cn/direct_messages/destroy/' . $did . '.json' );
    }

    /**
     * ����ɾ��˽��
     *
     * ����ɾ����ǰ��¼�û���˽�š������쳣ʱ������HTTP400����
     * <br />��ӦAPI��direct_messages/destroy_batch
     * 
     * @access public
     * @param mixed $dids ��ɾ����һ��˽��ID���ð�Ƕ��Ÿ�����������һ������ID��ɵ����顣���20�������磺"4976494627,4976262053"��array(4976494627,4976262053);
     * @return array
     */
    function delete_dms( $dids )
    {
        $params = array();
        if (is_array($dids) && !empty($dids)) {
            foreach($dids as $k => $v) {
                $this->id_format($dids[$k]);
            }
            $params['ids'] = join(',', $dids);
        } else {
            $params['ids'] = $dids;
        }

        return $this->oauth->post( 'https://api.t.sina.com.cn/direct_messages/destroy_batch.json' , $params );
    }

    /**
     * ��ȡ�û�����ת����n��΢����Ϣ
     *
     * ��ӦAPI��statuses/repost_by_me
     * 
     * @access public
     * @param int64 $uid Ҫ��ȡת��΢���б���û�ID��
     * @param int $page ҳ�롣��ѡ��
     * @param int $count ÿ�η��ص�����¼������෵��200����Ĭ��20����ѡ��
     * @param int64 $since_id ��ָ���˲�������ֻ����ID��since_id��ļ�¼����since_id����ʱ��������ѡ��
     * @param int64 $max_id ��ָ���˲������򷵻�IDС�ڻ����max_id�ļ�¼����ѡ��
     * @return array
     */
    function repost_by_me( $uid, $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $this->id_format($uid);

        $params = array();
        $params['id'] = $uid;
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'https://api.t.sina.com.cn/statuses/repost_by_me.json' , $page , $count , $params );
    }

    /**
     * ����һ��ԭ��΢��������n��ת��΢����Ϣ
     *
     * ��ӦAPI��statuses/repost_timeline
     * 
     * @access public
     * @param int64 $sid Ҫ��ȡת��΢���б��ԭ��΢��ID��
     * @param int $page ҳ�롣��ѡ��
     * @param int $count ÿ�η��ص�����¼������෵��200����Ĭ��20����ѡ��
     * @param int64 $since_id ��ָ���˲�������ֻ����ID��since_id��ļ�¼����since_id����ʱ��������ѡ��
     * @param int64 $max_id ��ָ���˲������򷵻�IDС�ڻ����max_id�ļ�¼����ѡ��
     * @return array
     */
    function repost_timeline( $sid, $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $this->id_format($sid);

        $params = array();
        $params['id'] = $sid;
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'https://api.t.sina.com.cn/statuses/repost_timeline.json' , $page , $count , $params );
    }

    /**
     * ת��һ��΢����Ϣ��
     *
     * �ɼ����ۡ�Ϊ��ֹ�ظ�����������Ϣ��������Ϣһ���������ᱻ���ԡ�
     * <br />��ӦAPI��statuses/repost
     * 
     * @access public
     * @param int64 $sid ת����΢��ID
     * @param string $text ��ӵ�������Ϣ����ѡ��
     * @param int $is_comment �Ƿ���ת����ͬʱ�������ۡ�1��ʾ�������ۣ�0��ʾ������Ĭ��Ϊ0����ѡ��
     * @return array
     */
    function repost( $sid , $text = NULL, $is_comment = 0 )
    {
        $this->id_format($sid);

        $params = array();
        $params['id'] = $sid;
        $params['is_comment'] = $is_comment;
        if( $text ) $params['status'] = $text;

        return $this->oauth->post( 'https://api.t.sina.com.cn/statuses/repost.json' , $params  );
    }

    /**
     * ��һ��΢����Ϣ��������
     *
     * Ϊ��ֹ�ظ�����������Ϣ�����һ��������Ϣһ���������ᱻ���ԡ�
     * <br />��ӦAPI��statuses/comment
     * 
     * @access public
     * @param int64 $sid Ҫ���۵�΢��id
     * @param string $text ��������
     * @param int64 $cid Ҫ���۵�����id
     * @return array
     */
    function send_comment( $sid , $text , $cid = NULL )
    {
        $this->id_format($sid);

        $params = array();
        $params['id'] = $sid;
        $params['comment'] = $text;
        if( $cid ) {
            $this->id_format($cid);
            $params['cid'] = $cid;
        }

        return $this->oauth->post( 'https://api.t.sina.com.cn/statuses/comment.json' , $params  );

    }

    /**
     * ����ɾ����ǰ�û���΢��������Ϣ
     *
     * ����ɾ�����ۡ�ע�⣺ֻ��ɾ����¼�û��Լ����������ۣ�������ɾ�������˵����ۡ�
     * <br />��ӦAPI��comment/destroy_batch
     * 
     * @access public
     * @param mixed $cids ��ɾ����һ������ID���ð�Ƕ��Ÿ�����������һ������ID��ɵ����顣���20�������磺"4976494627,4976262053"��array(4976494627,4976262053);
     * @return array
     */
    function comment_destroy_batch( $cids )
    {
        $params = array();
        if (is_array($cids) && !empty($cids)) {
            foreach ($cids as $k => $v) {
                $this->id_format($cids[$k]);
            }
            $params['ids'] = join(',', $cids);
        } else {
            $params['ids'] = $cids;
        }

        return $this->oauth->post( 'https://api.t.sina.com.cn/statuses/comment/destroy_batch.json' , $params );
    }

    /**
     * ��ȡ��ǰ�û�δ����Ϣ��
     *
     * ��ȡ��ǰ�û�Webδ����Ϣ��������@�ҵ�, �����ۣ���˽�ţ��·�˿����
     * <br />��ӦAPI��statuses/unread
     * 
     * @access public
     * @param int $with_new_status 1��ʾ����а���new_status�ֶΣ�0��ʾ���������new_status�ֶΡ�new_status�ֶα�ʾ�Ƿ�����΢����Ϣ��1��ʾ�У�0��ʾû�С�Ĭ��Ϊ0����ѡ��
     * @param int64 $since_id ����ֵΪ΢��id���ò��������with_new_status����ʹ�ã�����since_id֮���Ƿ�����΢����Ϣ��������ѡ��
     * @return array
     */
    function unread( $with_new_status = 0, $since_id = NULL )
    {
        $params = array();
        if ( $with_new_status ) {
            $params['with_new_status'] = $with_new_status;
            if ( $since_id ) {
                $this->id_format($since_id);
                $params['since_id'] = $since_id;
            }
        }

        return $this->oauth->get( 'https://api.t.sina.com.cn/statuses/unread.json' , $params );
    }

    /**
     * ��һ��΢��������Ϣ���лظ���
     *
     * Ϊ��ֹ�ظ�����������Ϣ�����һ������/�ظ���Ϣһ���������ᱻ���ԡ�
     * <br />��ӦAPI��statuses/reply
     * 
     * @access public
     * @param int64 $sid ΢��id
     * @param string $text �������ݡ�
     * @param int64 $cid ����id
     * @return array
     */
    function reply( $sid , $text , $cid )
    {
        $this->id_format($sid);
        $this->id_format($cid);
        $params = array();
        $params['id'] = $sid;
        $params['comment'] = $text;
        $params['cid'] = $cid;

        return $this->oauth->post( 'https://api.t.sina.com.cn/statuses/reply.json' , $params  );

    }

    /**
     * ��ȡ��ǰ�û����ղ��б�
     *
     * �����û��ķ��������20���ղ���Ϣ�����û��ղ�ҳ�淵��������һ�µġ�
     * <br />��ӦAPI��favorites
     * 
     * @access public
     * @param int $page ���ؽ����ҳ��š���ѡ��
     * @return array
     */
    function get_favorites( $page = NULL )
    {
        $params = array();
        if( $page ) $params['page'] = intval($page);

        return $this->oauth->get( 'https://api.t.sina.com.cn/favorites.json', $params );
    }

    /**
     * ɾ����ǰ�û���΢��������Ϣ��
     *
     * ע�⣺ֻ��ɾ���Լ����������ۣ�����΢�����û�������ɾ�������˵����ۡ�
     * <br />��ӦAPI��statuses/comment_destroy
     * 
     * @access public
     * @param int64 $cid Ҫɾ��������id
     * @return array
     */
    function comment_destroy( $cid )
    {
        $this->id_format($cid);
        return $this->oauth->post( 'https://api.t.sina.com.cn/statuses/comment_destroy/' . $cid . '.json' );
    }

    /**
     * ��ȡ��ǰ�û��յ�������
     *
     * ��ӦAPI��statuses/comments_to_me
     * 
     * @access public
     * @param int $page ҳ��
     * @param int $count ÿ�η��ص�����¼������෵��200����Ĭ��20��
     * @param int64 $since_id ��ָ���˲�������ֻ����ID��since_id������ۣ���since_id����ʱ��������ѡ��
     * @param int64 $max_id ��ָ���˲������򷵻�IDС�ڻ����max_id�����ۡ���ѡ��
     * @return array
     */
    function comments_to_me( $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'https://api.t.sina.com.cn/statuses/comments_to_me.json' , $page , $count , $params );
    }


    /**
     * ��ȡ��ǰ�û�����������
     *
     * ��ӦAPI��statuses/comments_by_me
     * 
     * @access public
     * @param int $page ҳ��
     * @param int $count ÿ�η��ص�����¼������෵��200����Ĭ��20��
     * @param int64 $since_id ��ָ���˲�������ֻ����ID��since_id������ۣ���since_id����ʱ��������ѡ��
     * @param int64 $max_id ��ָ���˲������򷵻�IDС�ڻ����max_id�����ۡ���ѡ��
     * @return array
     */
    function comments_by_me( $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'https://api.t.sina.com.cn/statuses/comments_by_me.json' , $page , $count , $params );
    }

    /**
     * ��������(��ʱ��)
     *
     * ��������n�����ͼ��յ������ۡ�
     * <br />��ӦAPI��statuses/comments_timeline
     * 
     * @access public
     * @param int $page ҳ��
     * @param int $count ÿ�η��ص�����¼������෵��200����Ĭ��20��
     * @param int64 $since_id ��ָ���˲�������ֻ����ID��since_id������ۣ���since_id����ʱ��������ѡ��
     * @param int64 $max_id ��ָ���˲������򷵻�IDС�ڻ����max_id�����ۡ���ѡ��
     * @return array
     */
    function comments_timeline( $page = 1 , $count = 20, $since_id = NULL, $max_id = NULL )
    {
        $params = array();
        if ($since_id) {
            $this->id_format($since_id);
            $params['since_id'] = $since_id;
        }
        if ($max_id) {
            $this->id_format($max_id);
            $params['max_id'] = $max_id;
        }

        return $this->request_with_pager( 'https://api.t.sina.com.cn/statuses/comments_timeline.json' , $page , $count , $params );
    }

    /**
     * ����΢���������б�
     *
     * ��ӦAPI��statuses/comments
     * 
     * @access public
     * @param mixed $sid ָ����΢��ID
     * @param int $page ҳ��
     * @param int $count ÿ�η��ص�����¼������෵��200����Ĭ��20��
     * @return array
     */
    function get_comments_by_sid( $sid , $page = 1 , $count = 20 )
    {
        $this->id_format($sid);
        $params = array();
        $params['id'] = $sid;
        if( $page ) $params['page'] = $page;
        if( $count ) $params['count'] = $count;

        return $this->oauth->get('https://api.t.sina.com.cn/statuses/comments.json' , $params );

    }

    /**
     * ������ȡһ��΢������������ת����
     *
     * ����ͳ��΢������������ת������һ����������ȡ100����
     * <br />��ӦAPI��statuses/counts
     * 
     * @access public
     * @param mixed $sids ΢��ID���б��ö��Ÿ�������ʹ�����ݴ���һ��΢��ID���磺"32817222,32817223"��array(32817222, 32817223)
     * @return array
     */
    function get_count_info_by_ids( $sids )
    {
        $params = array();
        if (is_array($sids) && !empty($sids)) {
            foreach ($sids as $k => $v) {
                $this->id_format($sids[$k]);
            }
            $params['ids'] = join(',', $sids);
        } else {
            $params['ids'] = $sids;
        }

        return $this->oauth->get( 'https://api.t.sina.com.cn/statuses/counts.json' , $params );
    }

    /**
     * �ղ�һ��΢����Ϣ
     *
     * ��ӦAPI��favorites/create
     * 
     * @access public
     * @param int64 $sid �ղص�΢��id
     * @return array
     */
    function add_to_favorites( $sid )
    {
        $this->id_format($sid);
        $params = array();
        $params['id'] = $sid;

        return $this->oauth->post( 'https://api.t.sina.com.cn/favorites/create.json' , $params );
    }

    /**
     * ����ɾ��΢���ղء�
     *
     * ����ɾ����ǰ��¼�û����ղء������쳣ʱ������HTTP400����
     * <br />��ӦAPI��favorites/destroy_batch
     * 
     * @access public
     * @param mixed $fids ��ɾ����һ��˽��ID���ð�Ƕ��Ÿ�����������һ������ID��ɵ����顣���20�������磺"231101027525486630,201100826122315375"��array(231101027525486630,201100826122315375);
     * @return array
     */
    function remove_from_favorites_batch( $fids )
    {
        $params = array();
        if (is_array($fids) && !empty($fids)) {
            foreach ($fids as $k => $v) {
                $this->id_format($fids[$k]);
            }
            $params['ids'] = join(',', $fids);
        } else {
            $params['ids'] = $fids;
        }

        return $this->oauth->post( 'https://api.t.sina.com.cn/favorites/destroy_batch.json' , $params );
    }

    /**
     * ɾ��΢���ղء�
     *
     * ��ӦAPI��favorites/destroy
     * 
     * @access public
     * @param int64 $id Ҫɾ�����ղ�΢����ϢID.
     * @return array
     */
    function remove_from_favorites( $id )
    {
        $this->id_format($id);
        return $this->oauth->post( 'https://api.t.sina.com.cn/favorites/destroy/' . $id . '.json'  );
    }

    /**
     * ��֤��ǰ�û�����Ƿ�Ϸ�
     *
     * ����û�����ͨ��֤�����֤�ɹ����û��Ѿ���ͨ΢���򷵻� http״̬Ϊ 200������ǲ��򷵻�401��״̬�ʹ�����Ϣ���˷��������ж��û�����Ƿ�Ϸ����Ѿ���ͨ΢����
     * <br />��ӦAPI��account/verify_credentials
     * 
     * @access public
     * @return array
     */
    function verify_credentials()
    {
        return $this->oauth->get('https://api.t.sina.com.cn/account/verify_credentials.json');
    }

    /**
     * ��ȡ��ǰ�û�API����Ƶ������
     *
     * ����API�ķ���Ƶ�����ơ����ص�ǰСʱ���ܷ��ʵĴ�����Ƶ�������Ǹ����û��������������ƣ�������Բμ�Ƶ������˵����
     * <br />��ӦAPI��account/rate_limit_status
     * 
     * @access public
     * @return array
     */
    function rate_limit_status()
    {
        return $this->oauth->get('https://api.t.sina.com.cn/account/rate_limit_status.json');
    }

    /**
     * ��ǰ�û��˳���¼
     *
     * �������֤�û���session���˳���¼������cookie��ΪNULL����Ҫ����widget��webӦ�ó��ϡ�
     * <br />��ӦAPI��account/end_session
     * 
     * @access public
     * @return array
     */
    function end_session()
    {
        return $this->oauth->post('https://api.t.sina.com.cn/account/end_session.json');
    }

    /**
     * ������˽��Ϣ
     *
     * ��ӦAPI��account/update_privacy
     * 
     * @access public
     * @param array $privacy_settings Ҫ�޸ĵ���˽���á���ʽ��array('key1'=>'value1', 'key2'=>'value2', .....)��<br />
     * ֧�����õ��<br />
     *  - description һ�仰����. ��ѡ����. ������160������.
     *  - comment: ˭�������۴��˺ŵ�΢���� 0�������� 1���ҹ�ע���� Ĭ��Ϊ0
     *  - message:˭���Ը����˺ŷ�˽�š�0�������� 1���ҹ�ע���� Ĭ��Ϊ1
     *  - realname �Ƿ��������ͨ����ʵ�����������ҡ�0������1��������Ĭ��ֵ1
     *  - geo ����΢�����Ƿ�����΢�����沢��ʾ�����ĵ���λ����Ϣ��0������1��������Ĭ��ֵ0
     *  - badge ѫ��չ��״̬��0������״̬��1��˽��״̬��Ĭ��ֵ0
     * @return array
     */
    function update_privacy($privacy_settings)
    {
        return $this->oauth->post('https://api.t.sina.com.cn/account/update_privacy.json', $privacy_settings);
    }

    /**
     * ��ȡ��˽��Ϣ�������
     *
     * ��ӦAPI��account/get_privacy
     * 
     * @access public
     * @return array
     */
    function get_privacy()
    {
        return $this->oauth->post('https://api.t.sina.com.cn/account/get_privacy.json');
    }

    /**
     * ����ͷ��
     *
     * ��ӦAPI��account/update_profile_image
     * 
     * @access public
     * @param string $image_path Ҫ�ϴ���ͷ��·��,֧��url��[ֻ֧��png/jpg/gif���ָ�ʽ,���Ӹ�ʽ���޸�get_image_mime����]
     * @return array
     */
    function update_profile_image($image_path)
    {
        $params = array();
        $params['image'] = "@{$image_path}";

        return $this->oauth->post('https://api.t.sina.com.cn/account/update_profile_image.json', $params, true);
    }

    /**
     * �����û�����
     *
     * ��ӦAPI��account/update_profile
     * 
     * @access public
     * @param array $profile Ҫ�޸ĵ����ϡ���ʽ��array('key1'=>'value1', 'key2'=>'value2', .....)��<br />
     * ֧���޸ĵ��<br />
     *  - name �ǳƣ���ѡ����.������20������<br />
     *  - gender �Ա𣬿�ѡ����. m,�У�f,Ů��<br />
     *  - province ����ʡ. ��ѡ����. �ο�ʡ�ݳ��б����<br />
     *  - city ���ڳ���. ��ѡ����. �ο�ʡ�ݳ��б����,1000Ϊ����<br />
     *  - description һ�仰����. ��ѡ����. ������160������.
     * @return array
     */
    function update_profile($profile)
    {
        return $this->oauth->post('https://api.t.sina.com.cn/account/update_profile.json', $profile);
    }

    /**
     * ʡ�ݳ��б����
     *
     * ��ȡʡ�ݼ����б���ID�����ֶ�Ӧ������΢���ӿ��û�province, city�ֶ����ü����ض���ID��API���÷���Ҫ��ʾʱת���ɶ�Ӧ���֡�ת����ϵ����
     * <br />��ӦAPI��provinces
     * 
     * @access public
     * @return array
     */
    function provinces()
    {
        return $this->oauth->get('https://api.t.sina.com.cn/provinces.json');
    }

    /**
     * �����û���ע����uid�б�
     *
     * ���û���ṩcursor��������ֻ������ǰ���5000����עid
     * <br />��ӦAPI��friends/ids
     * 
     * @access public
     * @param int $cursor ��ҳֻ�ܰ���5000��id��Ϊ�˻�ȡ������cursorĬ�ϴ�-1��ʼ��ͨ�����ӻ����cursor����ȡ����Ĺ�ע�б�
     * @param int $count ÿ�η��ص�����¼������ҳ���С����������5000,Ĭ�Ϸ���500��
     * @param mixed $uid_or_name  Ҫ��ȡ�� UID��΢���ǳ�
     * @return array
     */
    function friends_ids( $cursor = NULL , $count = 500 , $uid_or_name = NULL )
    {
        return $this->request_with_uid( 'https://api.t.sina.com.cn/friends/ids.json' ,  $uid_or_name , false , $count , $cursor );
    }

    /**
     * �����û���˿uid�б�
     *
     * ���û���ṩcursor��������ֻ������ǰ���5000����˿id
     * <br />��ӦAPI��followers/ids
     * 
     * @access public
     * @param int $cursor ��ҳֻ�ܰ���5000��id��Ϊ�˻�ȡ������cursorĬ�ϴ�-1��ʼ��ͨ�����ӻ����cursor����ȡ����ķ�˿�б�
     * @param int $count ÿ�η��ص�����¼������ҳ���С����������5000,Ĭ�Ϸ���500��
     * @param mixed $uid_or_name  Ҫ��ȡ�� UID��΢���ǳ�
     * @return array
     */
    function followers_ids( $cursor = NULL , $count = 500 , $uid_or_name = NULL )
    {
        return $this->request_with_uid( 'https://api.t.sina.com.cn/followers/ids.json' ,  $uid_or_name , false , $count , $cursor );
    }

    /**
     * ���û����������
     *
     * ��ӦAPI��blocks/create
     * 
     * @access public
     * @param int64 $user_id Ҫ������������û�ID����ѡ��$user_id��$screen_name������һ����
     * @param string $screen_name Ҫ������������û�΢���ǳƣ���ѡ��$user_id��$screen_name������һ����
     * @return array
     */
    function add_to_blocks( $user_id = NULL, $screen_name = NULL )
    {
        $this->id_format($user_id);

        $params = array();
        if ( $user_id ) $params['user_id'] = $user_id;
        if ( $screen_name ) $params['screen_name'] = $screen_name;

        return $this->oauth->post( 'https://api.t.sina.com.cn/blocks/create.json' , $params );
    }

    /**
     * ���û��Ƴ�������
     *
     * ��ӦAPI��blocks/destroy
     * 
     * @access public
     * @param int64 $user_id Ҫ�Ƴ����������û�ID����ѡ��$user_id��$screen_name������һ����
     * @param string $screen_name Ҫ�Ƴ����������û�΢���ǳƣ���ѡ��$user_id��$screen_name������һ����
     * @return array
     */
    function remove_from_blocks( $user_id = NULL, $screen_name = NULL )
    {
        $this->id_format($user_id);

        $params = array();
        if ( $user_id ) $params['user_id'] = $user_id;
        if ( $screen_name ) $params['screen_name'] = $screen_name;

        return $this->oauth->post( 'https://api.t.sina.com.cn/blocks/destroy.json' , $params );
    }

    /**
     * ����Ƿ��Ǻ������û�
     *
     * ��ӦAPI��blocks/exists
     * 
     * @access public
     * @param int64 $user_id Ҫ�����û�ID����ѡ��$user_id��$screen_name������һ����
     * @param string $screen_name Ҫ�����û�΢���ǳƣ���ѡ��$user_id��$screen_name������һ����
     * @return array
     */
    function in_blocks( $user_id = NULL, $screen_name = NULL )
    {
        $this->id_format($user_id);

        $params = array();
        if ( $user_id ) $params['user_id'] = $user_id;
        if ( $screen_name ) $params['screen_name'] = $screen_name;

        return $this->oauth->post( 'https://api.t.sina.com.cn/blocks/exists.json' , $params );
    }

    /**
     * �г��������û�(����û���ϸ��Ϣ)��
     *
     * ��ӦAPI��blocks/blocking
     * 
     * @access public
     * @param int $page ָ�����ؽ����ҳ�롣��ѡ��
     * @param int $count ��ҳ��С��ȱʡֵ20�����ֵ200����ѡ��
     * @return array
     */
    function get_blocks( $page = 1, $count = 20 )
    {
        return $this->request_with_pager( 'https://api.t.sina.com.cn/blocks/blocking.json' , $page , $count );
    }

    /**
     * �г��������û�(ֻ���id)��
     *
     * ��ӦAPI��blocks/blocking/ids
     * 
     * @access public
     * @param int $page ָ�����ؽ����ҳ�롣��ѡ��
     * @param int $count ��ҳ��С��ȱʡֵ20�����ֵ200����ѡ��
     * @return array
     */
    function get_block_ids( $page = 1, $count = 20 )
    {
        return $this->request_with_pager( 'https://api.t.sina.com.cn/blocks/blocking/ids.json' , $page , $count );
    }

    /**
     * ����ָ���û��ı�ǩ�б�
     *
     * ��ӦAPI��tags
     * 
     * @access public
     * @param int64 $user_id ��ѯ�û���ID��Ĭ��Ϊ��ǰ�û�����ѡ��
     * @param int $page ָ�����ؽ����ҳ�롣��ѡ��
     * @param int $count ��ҳ��С��ȱʡֵ20�����ֵ200����ѡ��
     * @return array
     */
    function get_tags( $user_id = NULL, $page = 1, $count = 20 )
    {
        $params = array();
        if ($user_id) {
            $params['user_id'] = $user_id;
        } else {
            $user_info = $this->verify_credentials();
            $params['user_id'] = $user_info['id'];
        }
        $this->id_format($params['user_id']);
        return $this->request_with_pager( 'https://api.t.sina.com.cn/tags.json' , $page , $count , $params );
    }

    /**
     * ����û���ǩ
     *
     * ��ӦAPI��tags/create
     * 
     * @access public
     * @param mixed $tags ��ǩ�������ǩ֮���ö��ż�������ɶ����ǩ���ɵ����顣�磺"abc,drf,efgh,tt"��array("abc","drf","efgh","tt")
     * @return array
     */
    function add_tags( $tags )
    {
        $params = array();
        if (is_array($tags) && !empty($tags)) {
            $params['tags'] = join(',', $tags);
        } else {
            $params['tags'] = $tags;
        }
        return $this->oauth->post( 'https://api.t.sina.com.cn/tags/create.json' , $params );
    }

    /**
     * �����û�����Ȥ�ı�ǩ
     *
     * ��ӦAPI��tags/suggestions
     * 
     * @access public
     * @param int $page ָ�����ؽ����ҳ�롣��ѡ��
     * @param int $count ��ҳ��С��ȱʡֵ10�����ֵ200����ѡ��
     * @return array
     */
    function get_suggest_tags( $page = 1, $count = 10 )
    {
        return $this->request_with_pager( 'https://api.t.sina.com.cn/tags/suggestions.json' , $page , $count );
    }

    /**
     * ɾ����ǩ
     *
     * ��ӦAPI��tags/destroy
     * 
     * @access public
     * @param int $tag_id ��ǩID���������
     * @return array
     */
    function delete_tag( $tag_id )
    {
        $params = array();
        $params['tag_id'] = $tag_id;
        return $this->oauth->post( 'https://api.t.sina.com.cn/tags/destroy.json' , $params );
    }

    /**
     * ����ɾ����ǩ
     *
     * ��ӦAPI��tags/destroy_batch
     * 
     * @access public
     * @param mixed $ids ��ѡ������Ҫɾ����tag id�����id�ð�Ƕ��ŷָ���20�������ɶ��tag id���ɵ����顣�磺��553,554,555"��array(553,554,555)
     * @return array
     */
    function delete_tags( $ids )
    {
        $params = array();
        if (is_array($ids) && !empty($ids)) {
            $params['ids'] = join(',', $ids);
        } else {
            $params['ids'] = $ids;
        }
        return $this->oauth->post( 'https://api.t.sina.com.cn/tags/destroy_batch.json' , $params );
    }

    /**
     * ��ȡĳ�û��Ļ���
     *
     * ��ӦAPI��trends
     * 
     * @access public
     * @param int64 $user_id ��ѯ�û���ID��Ĭ��Ϊ��ǰ�û�����ѡ��
     * @param int $page ָ�����ؽ����ҳ�롣��ѡ��
     * @param int $count ��ҳ��С��ȱʡֵ10����ѡ��
     * @return array
     */
    function get_trends( $user_id = NULL, $page = 1, $count = 20 )
    {
        $params = array();
        if ($user_id) {
            $params['user_id'] = $user_id;
        } else {
            $user_info = $this->verify_credentials();
            $params['user_id'] = $user_info['id'];
        }
        $this->id_format($params['user_id']);
        return $this->request_with_pager( 'https://api.t.sina.com.cn/trends.json' , $page , $count , $params );
    }

    /**
     * ��ȡĳ�����µ�΢����Ϣ
     *
     * ��ӦAPI��trends/statuses
     * 
     * @access public
     * @param string $trend_name ����ؼ��ʡ�
     * @return array
     */
    function trends_timeline( $trend_name )
    {
        $params = array();
        $params['trend_name'] = $trend_name;

        return $this->oauth->get( 'https://api.t.sina.com.cn/trends/statuses.json' , $params );
    }

    /**
     * ��עĳ����
     *
     * ��ӦAPI��trends/follow
     * 
     * @access public
     * @param string $trend_name Ҫ��ע�Ļ���ؼ��ʡ�
     * @return array
     */
    function follow_trends( $trend_name )
    {
        $params = array();
        $params['trend_name'] = $trend_name;

        return $this->oauth->post( 'https://api.t.sina.com.cn/trends/follow.json' , $params );
    }

    /**
     * ȡ����ĳ����Ĺ�ע
     *
     * ��ӦAPI��trends/destroy
     * 
     * @access public
     * @param int64 $tid Ҫȡ����ע�Ļ���ID��
     * @return array
     */
    function unfollow_trends( $tid )
    {
        $this->id_format($tid);

        $params = array();
        $params['trend_id'] = $tid;

        return $this->oauth->delete( 'https://api.t.sina.com.cn/trends/destroy.json' , $params );
    }

    /**
     * �������һСʱ�ڵ����Ż���
     *
     * ��ӦAPI��trends/hourly
     * 
     * @access public
     * @param int $base_app �Ƿ���ڵ�ǰӦ������ȡ���ݡ�1��ʾ���ڵ�ǰӦ������ȡ���ݣ�Ĭ��Ϊ1����ѡ��
     * @return array
     */
    function hourly_trends( $base_app = 1 )
    {
        $params = array();
        $params['base_app'] = $base_app;

        return $this->oauth->get( 'https://api.t.sina.com.cn/trends/hourly.json' , $params );
    }

    /**
     * �������һ���ڵ����Ż���
     *
     * ��ӦAPI��trends/daily
     * 
     * @access public
     * @param int $base_app �Ƿ���ڵ�ǰӦ������ȡ���ݡ�1��ʾ���ڵ�ǰӦ������ȡ���ݣ�Ĭ��Ϊ1����ѡ��
     * @return array
     */
    function daily_trends( $base_app = 1 )
    {
        $params = array();
        $params['base_app'] = $base_app;

        return $this->oauth->get( 'https://api.t.sina.com.cn/trends/daily.json' , $params );
    }

    /**
     * �������һ���ڵ����Ż���
     *
     * ��ӦAPI��trends/weekly
     * 
     * @access public
     * @param int $base_app �Ƿ���ڵ�ǰӦ������ȡ���ݡ�1��ʾ���ڵ�ǰӦ������ȡ���ݣ�Ĭ��Ϊ1����ѡ��
     * @return array
     */
    function weekly_trends( $base_app = 1 )
    {
        $params = array();
        $params['base_app'] = $base_app;

        return $this->oauth->get( 'https://api.t.sina.com.cn/trends/weekly.json' , $params );
    }

    // =========================================

    /**
     * @ignore
     */
    protected function request_with_pager( $url , $page = false , $count = false , $params = array() )
    {
        if( $page ) $params['page'] = $page;
        if( $count ) $params['count'] = $count;

        return $this->oauth->get($url , $params );
    }

    /**
     * @ignore
     */
    protected function request_with_uid( $url , $uid_or_name , $page = false , $count = false , $cursor = false , $post = false , $params = array())
    {
        if( $page ) $params['page'] = $page;
        if( $count ) $params['count'] = $count;
        if( $cursor )$params['cursor'] =  $cursor;

        if( $post ) $method = 'post';
        else $method = 'get';

        if ( $uid_or_name !== NULL ) {
            $this->id_format($uid_or_name);
            $params['id'] = $uid_or_name;
        }

        return $this->oauth->$method($url , $params );

    }

    protected function id_format(&$id) {
        if ( is_float($id) ) {
            $id = number_format($id, 0, '', '');
        } elseif ( is_string($id) ) {
            $id = trim($id);
        }
    }

}

/**
 * ����΢�� OAuth ��֤��(��)
 * 
 * @ignore
 */
class SaeT extends SaeTOAuth
{
    function __construct( $client_id, $client_secret, $access_token = NULL, $refresh_token = NULL )
    {
        parent::__construct( $client_id, $client_secret, $access_token , $refresh_token );
    }
}
