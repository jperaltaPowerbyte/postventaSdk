<?php

namespace InesPostventa;

use Exception;
use Freshdesk\Api;
use Freshdesk\Exceptions\AccessDeniedException;
use Freshdesk\Exceptions\ApiException;
use Freshdesk\Exceptions\AuthenticationException;
use Freshdesk\Exceptions\ConflictingStateException;
use Freshdesk\Exceptions\MethodNotAllowedException;
use Freshdesk\Exceptions\NotFoundException;
use Freshdesk\Exceptions\RateLimitExceededException;
use Freshdesk\Exceptions\UnsupportedAcceptHeaderException;
use Freshdesk\Exceptions\UnsupportedContentTypeException;
use Freshdesk\Exceptions\ValidationException;

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

    private $freshdesk;

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

        $this->freshdesk = new Api("uyOmDjijr4GpL5FDFlmV", "powerbyte");
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
            $this->userData = (object)$userData['retailer'];
        } else {
            throw new Exception('Error de credenciales.');
        }

    }

    public function tickets()
    {
        if ($this->userData) {
            if ($tickets = $this->sendRequest("tickets/{$this->userData->id}", 'get')) {
                foreach ($tickets['tickets'] as $index => $ticket) {
                    if ($ticket['freshdesk_id']) {
                        $tickets[$index]['freshdesk_data'] = $this->freshdesk->tickets->view($ticket['freshdesk_id']);
                    }
                }

                return $tickets;
            }

        }

        return [];
    }

    /**
     * @param $ticket_id
     * @return array|bool|false|mixed|string
     * @throws AccessDeniedException
     * @throws ApiException
     * @throws AuthenticationException
     * @throws ConflictingStateException
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     * @throws RateLimitExceededException
     * @throws UnsupportedAcceptHeaderException
     * @throws UnsupportedContentTypeException
     * @throws ValidationException
     */
    public function ticket($ticket_id)
    {
        if ($this->userData) {
            if ($ticket = $this->sendRequest("tickets/{$this->userData->id}/{$ticket_id}", 'get')) {
                if ($ticket['freshdesk_id']) {
                    $ticket['freshdesk_data'] = $this->freshdesk->tickets->view($ticket['freshdesk_id']);
                }
            }
            return $ticket;
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

    /**
     * @param $ticketId
     * @param $statusId
     * @return bool
     * @throws AccessDeniedException
     * @throws ApiException
     * @throws AuthenticationException
     * @throws ConflictingStateException
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     * @throws RateLimitExceededException
     * @throws UnsupportedAcceptHeaderException
     * @throws UnsupportedContentTypeException
     * @throws ValidationException
     */
    public function updateTicketStatus($ticketId, $statusId)
    {

        if (!in_array($statusId, [2, 3, 4, 5])) {
            throw new Exception('Error: ingresar un estado de Freshdesk válido.');
        }

        if(!$ticketId){
            throw new Exception('Error: ingresar un ticket de Freshdesk válido.');
        }

        $responder = $this->freshdesk->agents->current();

        $ticket_data = array(
            "responder_id" => $responder['id'],
            "status" => $statusId,
        );

        try {
            $updated = $this->freshdesk->tickets->update($ticketId, $ticket_data);
            return true;
        } catch (AccessDeniedException $e) {
            var_dump($e);
        } catch (AuthenticationException $e) {
            var_dump($e);
        } catch (ConflictingStateException $e) {
            var_dump($e);
        } catch (MethodNotAllowedException $e) {
            var_dump($e);
        } catch (NotFoundException $e) {
            var_dump($e);
        } catch (RateLimitExceededException $e) {
            var_dump($e);
        } catch (UnsupportedAcceptHeaderException $e) {
            var_dump($e);
        } catch (UnsupportedContentTypeException $e) {
            var_dump($e);
        } catch (ValidationException $e) {
            var_dump($e);
        } catch (ApiException $e) {
            var_dump($e);
        }


    }
}