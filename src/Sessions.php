<?php

namespace Bermuda\Authentication;

/**
 * Class Sessions
 * @package Bermuda\Authentication
 */
final class Sessions implements \IteratorAggregate
{
    /**
     * @var SessionInterface[]
     */
    private array $sessions = [];
    private ?string $currentId = null;

    public function __construct(iterable $sessions, string $currentId = null)
    {
        $this->currentId = $currentId;

        foreach ($sessions as $session)
        {
            $this->add($session);
        }
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setCurrentId(string $id): self
    {
        $this->currentId = $id;
        return $this;
    }

    /**
     * @param SessionInterface $session
     */
    public function add(SessionInterface $session)
    {
        $this->sessions[$session->getId()] = $session;
    }

    /**
     * @param $id
     * @return SessionInterface|null
     */
    public function get($id):? SessionInterface
    {
        return $this->sessions[$id] ?? null;
    }

    /**
     * @return SessionInterface
     */
    public function current():? SessionInterface
    {
        foreach ($this->sessions as $session)
        {
            if ($this->currentId && $session->getId() == $this->currentId)
            {
                return $session;
            }
        }
        
        return null;
    }

    /**
     * @return SessionInterface[]
     */
    public function getIterator(): \Generator
    {
        foreach ($this->sessions as $id => $session)
        {
            yield $id => $session;
        }
    }
}
