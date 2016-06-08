<?php

namespace OldTimeGuitarGuy\Testing;

trait AssertEmail
{
    /**
     * Holds the guzzle client instance
     *
     * @var \GuzzleHttp\Client
     */
    protected $mailcatcher;

    ////////////////////
    // PUBLIC METHODS //
    ////////////////////

    /**
     * Get all emails
     * 
     * @return array
     */
    public function getAllEmails()
    {
        $emails = $this->jsonEmail($this->mailcatcher()->get('messages'));

        if ( empty($emails) ) {
            $this->fail('No messages returned');
        }

        return $emails;
    }

    /**
     * Delete all emails
     * 
     * @return boolean
     */
    public function deleteAllEmails()
    {
        return $this->mailcatcher()->delete('messages');
    }

    /**
     * Get the last email sent
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    public function getLastEmail()
    {
        $email_id = last($this->getAllEmails())->id;

        return $this->mailcatcher()->get("messages/{$email_id}.json");
    }

    /**
     * Go through each email, calling the callback along the way
     *
     * @param  \Closure $callback
     * @return void
     */
    public function eachEmail(\Closure $callback)
    {
        array_map(function($email) use ($callback) {
            $callback($this->mailcatcher()->get("messages/{$email->id}.json"));
        }, $this->getAllEmails());
    }

    ////////////////
    // ASSERTIONS //
    ////////////////

    /**
     * Assert the email subject contains a string
     * 
     * @param  string              $subject
     * @param  \GuzzleHttp\Psr7\Response $email
     */
    public function assertEmailSubjectContains($subject, \GuzzleHttp\Psr7\Response $email)
    {
        $this->assertContains($subject, $this->subject($email));
    }

    /**
     * Assert the email subject does not contain a string
     * 
     * @param  string              $subject
     * @param  \GuzzleHttp\Psr7\Response $email
     */
    public function assertNotEmailSubjectContains($subject, \GuzzleHttp\Psr7\Response $email)
    {
        $this->assertNotContains($subject, $this->subject($email));
    }

    /**
     * Assert the email body contains a string
     * 
     * @param  string              $body
     * @param  \GuzzleHttp\Psr7\Response $email
     */
    public function assertEmailBodyContains($body, \GuzzleHttp\Psr7\Response $email)
    {
        $this->assertContains($body, $this->body($email));
    }

    /**
     * Assert the email body doesn't contain a string
     * 
     * @param  string              $body
     * @param  \GuzzleHttp\Psr7\Response $email
     */
    public function assertNotEmailBodyContains($body, \GuzzleHttp\Psr7\Response $email)
    {
        $this->assertNotContains($body, $this->body($email));
    }

    /**
     * Assert email was sent to email address
     * 
     * @param  string              $recipient
     * @param  \GuzzleHttp\Psr7\Response $email
     */
    public function assertEmailWasSentTo($recipient, \GuzzleHttp\Psr7\Response $email)
    {
        $this->assertContains("<{$recipient}>", $this->recipients($email));
    }

    /**
     * Assert email was not sent to email address
     * @param  string              $recipient
     * @param  \GuzzleHttp\Psr7\Response $email
     */
    public function assertNotEmailWasSentTo($recipient, \GuzzleHttp\Psr7\Response $email)
    {
        $this->assertNotContains("<{$recipient}>", $this->recipients($email));
    }

    /**
     * Assert the email was sent from the sender
     * 
     * @param  string              $sender
     * @param  \GuzzleHttp\Psr7\Response $email
     */
    public function assertEmailWasSentFrom($sender, \GuzzleHttp\Psr7\Response $email)
    {
        $this->assertContains("<{$sender}>", $this->sender($email));
    }

    /**
     * Assert the email was not sent from the sender
     * 
     * @param  string              $sender
     * @param  \GuzzleHttp\Psr7\Response $email
     */
    public function assertNotEmailWasSentFrom($sender, \GuzzleHttp\Psr7\Response $email)
    {
        $this->assertContains("<{$sender}>", $this->sender($email));
    }

    /**
     * Assert that the email reply to contains the given email address
     *
     * @param  string                    $replyTo
     * @param  \GuzzleHttp\Psr7\Response $email
     */
    public function assertEmailReplyToContains($replyTo, \GuzzleHttp\Psr7\Response $email)
    {
        $this->assertRegExp("/Reply-To:\s.*?<?((?:[a-zA-Z0-9_\-\.]+)@(?:(?:\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(?:(?:[a-zA-Z0-9\-]+\.)+))(?:[a-zA-Z]{2,4}|[0-9]{1,3})(?:\]?))>?/", $this->body($email));
    }

    /**
     * Assert that the email reply to DOES NOT contain the given email address
     *
     * @param  string                    $replyTo
     * @param  \GuzzleHttp\Psr7\Response $email
     */
    public function assertEmailReplyToNotContains($replyTo, \GuzzleHttp\Psr7\Response $email)
    {
        $this->assertNotRegExp("/Reply-To:\s.*?<?((?:[a-zA-Z0-9_\-\.]+)@(?:(?:\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(?:(?:[a-zA-Z0-9\-]+\.)+))(?:[a-zA-Z]{2,4}|[0-9]{1,3})(?:\]?))>?/", $this->body($email));
    }

    ///////////////////////
    // PROTECTED METHODS //
    ///////////////////////

    /**
     * Get the json representation of the email
     * as a generic class
     * 
     * @param  \GuzzleHttp\Psr7\Response $email
     * @return \stdClass
     */
    protected function jsonEmail(\GuzzleHttp\Psr7\Response $email)
    {
        return json_decode((string)$email->getBody());
    }

    /**
     * Get the body of the email
     * 
     * @param  \GuzzleHttp\Psr7\Response $email
     * @return string
     */
    protected function body(\GuzzleHttp\Psr7\Response $email)
    {
        return $this->jsonEmail($email)->source;
    }

    /**
     * Get the subject of the email
     * 
     * @param  \GuzzleHttp\Psr7\Response $email
     * @return string
     */
    protected function subject(\GuzzleHttp\Psr7\Response $email)
    {
        return $this->jsonEmail($email)->subject;
    }

    /**
     * Get the recipients of the email
     * 
     * @param  \GuzzleHttp\Psr7\Response $email
     * @return array
     */
    protected function recipients(\GuzzleHttp\Psr7\Response $email)
    {
        return $this->jsonEmail($email)->recipients;
    }

    /**
     * Get the sender of the email
     * 
     * @param  \GuzzleHttp\Psr7\Response $email
     * @return string
     */
    protected function sender(\GuzzleHttp\Psr7\Response $email)
    {
        return $this->jsonEmail($email)->sender;
    }

    /**
     * Return the guzzle client that
     * interacts with mailcatcher.
     * If it doesn't exist, then create it.
     *
     * @return \GuzzleHttp\Client
     */
    protected function mailcatcher()
    {
        return $this->mailcatcher
            ?: $this->mailcatcher = new \GuzzleHttp\Client(['base_uri' => 'http://127.0.0.1:1080']);
    }
}
