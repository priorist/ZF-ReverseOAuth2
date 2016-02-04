<?php

namespace ReverseOAuth2\Client;

use ReverseOAuth2\AbstractOAuth2Client;
use Zend\Http\PhpEnvironment\Request;
use Zend\Json\Json;
use Zend\Json\Decoder as JsonDecoder;

class Instagram extends AbstractOAuth2Client
{
    protected $providerName = 'instagram';


    public function getUrl()
    {

        $url = $this->options->getAuthUri().'?'
            . 'redirect_uri='  . urlencode($this->options->getRedirectUri())
            . '&client_id='    . $this->options->getClientId()
            . '&state='        . $this->generateState()
            . '&response_type=code'
            . $this->getScope(',');

        return $url;

    }


    public function getToken(Request $request)
    {

        if(isset($this->session->token)) {

            return true;

        } elseif(strlen($this->session->state) > 0 AND $this->session->state == $request->getQuery('state') AND strlen($request->getQuery('code')) > 5) {

            $client = $this->getHttpClient();

            $client->setUri($this->options->getTokenUri());

            $client->setMethod(Request::METHOD_POST);

            $client->setParameterPost(array(
                'code'          => $request->getQuery('code'),
                'client_id'     => $this->options->getClientId(),
                'client_secret' => $this->options->getClientSecret(),
                'redirect_uri'  => $this->options->getRedirectUri(),
                'grant_type'    => 'authorization_code',
            ));

            $resBody = $client->send()->getBody();

            try {
                $response = JsonDecoder::decode($resBody, Json::TYPE_ARRAY);

                if (is_array($response) AND isset($response['access_token']) AND (!isset($response['expires']) || $response['expires'] > 0)) {
                    $this->session->token = (object)$response;
                    return true;
                } else {
                    $this->error = array(
                        'internal-error' => 'Instagram settings error.',
                        'message' => $response->error_message,
                        'type' => $response->error_type,
                        'code' => $response->code
                    );

                    return false;
                }

            } catch(\Zend\Json\Exception\RuntimeException $e) {
                $this->error = array(
                    'internal-error' => 'Parse error.',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                );

                return false;
            }

        } else {

            $this->error = array(
                'internal-error'=> 'State error, request variables do not match the session variables.',
                'session-state' => $this->session->state,
                'request-state' => $request->getQuery('state'),
                'code'          => $request->getQuery('code')
            );

            return false;

        }

    }

}
