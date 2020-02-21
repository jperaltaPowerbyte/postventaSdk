<?php

namespace InesPostventa;

use Exception;

class InesPostventa
{

    private $key;
    private $secret;
    private $sdkUri = 'https://ines.net.ar/postventa';
//    private $sdkUri = 'http://ines.postventa';
    private $userData;


    private $orderInfo = [];
    private $article = [];
    private $orderArticles = [];

    /**
     * PowerPayments constructor.
     * @param $key
     * @param $secret
     * @throws Exception
     */
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;

        $this->checkUserData();
    }

    /**
     * @param $sandbox
     */
    public function setEnvironment($sandbox)
    {
        if ($sandbox) {
            $this->sdkUri = str_replace('postventa', 'postventa.sandbox', $this->sdkUri);
        }
    }

    /**
     *
     * @throws Exception
     */
    private function checkUserData()
    {
        $userData = $this->sendRequest('checkCredentials', 'post', array(
            'key'    => $this->key,
            'secret' => $this->secret,
        ));

        if ($userData['retailer']) {
            $this->userData = (object) $userData['retailer'];
        } else {
            throw new Exception('Error de credenciales.');
        }

    }

    public function tickets()
    {
        if ($this->userData) {
            return $this->sendRequest("tickets/{$this->userData->id}", 'get');
        }

        return [];
    }

    public function ticket($ticket_id)
    {
        if ($this->userData) {
            return $this->sendRequest("tickets/{$this->userData->id}/{$ticket_id}", 'get');
        }

        return [];
    }

    /**
     * @return mixed
     */
    public function getUserData($array = false)
    {
        if ($array) {
            return $this->userData;
        }

        return (object)$this->userData;

    }

    /**
     * @return string
     */
    public function getSdkUri()
    {
        return $this->sdkUri;
    }

    /**
     * @param $request
     * @param string $method
     * @param array $data
     * @return bool|false|mixed|string
     */
    private function sendRequest($request, $method = 'get', $data = [])
    {
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => strtolower($method),
                'content' => http_build_query($data)
            )
        );

        $context = stream_context_create($options);

        $result = file_get_contents("{$this->sdkUri}/{$request}?" . http_build_query($data), false, $context);
//var_dump($result);
        $result = json_decode($result, true);

        if ($result === FALSE) {
            return false;
        }

        return $result;
    }

    /**
     * @param $header_items
     */
    public function setHeader($header_items)
    {
        foreach ($header_items as $key => $value) {
            if (array_key_exists($key, $this->orderInfo)) {
                $this->orderInfo[$key] = $value;
            }
        }
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->orderInfo;
    }

    /**
     * @param $article
     */
    public function setDetails($article)
    {
        foreach ($article as $key => $value) {
            if (array_key_exists($key, $this->article)) {
                $this->article[$key] = $value;

                if ($key == 'options') {
                    $options = [];

                    foreach ($value as $option => $val) {
                        $options[] = "$option:$val";
                    }

                    $this->article[$key] = implode('|', $options);
                }
            }
        }

        $this->orderArticles[] = $this->article;

        $this->clearArticle();
    }

    /**
     *
     */
    private function clearArticle()
    {
        foreach ($this->article as $key => $value) {
            $this->article[$key] = '';
        }
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->orderArticles;
    }

    /**
     * @return bool|false|mixed|string
     * @throws Exception
     */
    public function storeOrder()
    {
        $response = $this->sendRequest('orders/store', 'post', ['order_info' => $this->orderInfo, 'order_articles' => $this->orderArticles]);

        if ($response['order_id']) {
            return $response['order_id'];
        } else {
            throw new Exception('Error al guardar el pedido.');
        }
    }

    /**
     * @param $order_id
     */
    public function paymentFormRedirect($order_id)
    {
        header("Location:{$this->sdkUri}/paymentForm/{$order_id}");
    }
}