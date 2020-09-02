<?php


namespace App\Authentication;


/**
 * Class Sessions
 * @package App\Auth
 */
final class Sessions implements \IteratorAggregate
{
    /**
     * @var SessionInterface[]
     */
    private array $sessions = [];
    private ?string $currentId = null;

    public function __construct(iterable $sessions)
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
    public function current(): SessionInterface
    {
        foreach ($this->sessions as $session)
        {
            if ($this->currentId && $session->getId() == $this->currentId)
            {
                return $session;
            }
        }

        throw new \RuntimeException('');
    }

    /**
     * @return SessionInterface[]
     */
    public function getIterator(): \Generator
    {
        foreach ($this->sessions as $session)
        {
            yield $session;
        }
    }
}