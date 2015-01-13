<?php

class Card
{
    private $color;
    private $weight;
    private $frontImage;
    public static $backImage;
    private $isDealt = false;

    /**
     * Creates a card and sets its face.
     */
    public function __construct($color, $weight, $frontImage)
    {
        $this->color = $color;
        $this->weight = $weight;
        $this->frontImage = $frontImage;
    }

    public function getColor() { return $this->color; }
    public function getWeight() { return $this->weight; }
    public function getIsDealt() { return $this->isDealt; }
    public function setIsDealt($value) { $this->isDealt = $value; }
    public function getFrontImage() { return $this->frontImage; }

    public function __toString()
    {
        if (($this->weight >= 11) && ($this->weight <= 14))
        {
            return ($this->getConstantName("CardWeight", $this->weight) . " of " .
                $this->getConstantName("CardColor", $this->color));
        }
        else
        {
            return ($this->weight . " of " .
                $this->getConstantName("CardColor", $this->color));
        }
    }

    private static function getConstantName($class, $value)
    {
        $map = array_flip((new \ReflectionClass($class))->getConstants());
        return (array_key_exists($value, $map) ? $map[$value] : null);
    }
}

class CardColor //enum
{
    const Clubs = 0;
    const Spades = 1;
    const Diamonds = 2;
    const Hearts = 3;
}

class CardWeight //enum
{
    const Jack = 11;
    const Queen = 12;
    const King = 13;
    const Ace = 14;
}

class CardDeck implements Iterator, ArrayAccess
{
    private $deck = [];
    private $position = 0;

    /**
     * Creates an unshuffled 52 card deck.
     * Back of a card is set only once since the
     * changes are applied to the whole deck (static field).
     */
    public function __construct($frontImages, $backImage)
    {
        if (!empty($frontImages) && !empty($backImage) &&
            (count($frontImages) == 52))
        {
            $idx = 0;
            for ($i = 0; $i < 4; $i++)
            {
                for ($j = 2; $j <= 14; $j++)
                {
                    //$card = new Card($i, $j, $frontImages[$idx]);//frontImages alternative
					$card = new Card($i, $j, $this->buildImage($i, $j));
                    array_push($this->deck, $card);
                    $idx++;
                }
            }
            Card::$backImage = $backImage;
        }
        else throw new InvalidArgumentException();
    }
	
	/* build frontImage name */
	public function buildImage($i, $j)
    {
        if (($j >= 11) && ($j <= 14))
        {
            return strtolower($this->getConstantName("CardWeight", $j) . "_of_" .
                $this->getConstantName("CardColor", $i) . ".png");
        }
        else
        {
            return strtolower($j . "_of_" .
                $this->getConstantName("CardColor", $i) . ".png");
        }
    }
	
	private static function getConstantName($class, $value)
    {
        $map = array_flip((new \ReflectionClass($class))->getConstants());
        return (array_key_exists($value, $map) ? $map[$value] : null);
    }
	
    /**
     * Tries to get the required card from the deck.
     */
    public function getCard($cardColor, $cardWeight)
    {
        if (in_array($cardColor, range(0, 3)) &&
            in_array($cardWeight, range(2, 14)))
        {
            foreach ($this->deck as $card)
            {
                if (($card->getColor() == $cardColor) &&
                    ($card->getWeight() == $cardWeight))
                {
                    if ($card->getIsDealt())
                        throw new InvalidArgumentException(
                            "Card '$card' has already been dealt.");
                    else return $card;
                }
            }
        }
        else throw new InvalidArgumentException();
    }

    /**
     * Sets cards' property 'isDealt' to false.
     */
    public function getCardsBackToDeck()
    {
        foreach ($this->deck as $card)
            $card->setIsDealt(false);
    }

    /**
     * Tries to shuffle the deck. Uses PHP shuffle() function.
     * @param int $iterations Number of shuffles to perform.
     */
    public function shuffleDeck($iterations = 1)
    {
        if (!empty($this->deck))
        {
            if ($iterations < 1)
                throw new InvalidArgumentException("Number of iterations is less than 1.");
            for ($i = 0; $i < $iterations; $i++)
                shuffle($this->deck);
        }
        else throw new InvalidArgumentException("Deck contains no cards.");
    }

    /**
     * Iterator interface implementation
     */
    public function current() { return $this->deck[$this->position]; }
    public function next() { ++$this->position; }
    public function key() { return $this->position; }
    public function valid() { return isset($this->deck[$this->position]); }
    public function rewind() { $this->position = 0; }

    /**
     * ArrayAccess interface implementation
     */
    public function offsetExists($offset) { return isset($this->deck[$offset]); }
    public function offsetGet($offset)
    {
        if (isset($this->deck[$offset]))
            return $this->deck[$offset];
        else throw new InvalidArgumentException("Invalid offset: $offset");
    }
    public function offsetSet($offset, $value) { throw new Exception("Internal deck array cannot be modified directly."); }
    public function offsetUnset($offset) { throw new Exception("Internal deck array cannot be modified directly."); }
}

$deck = new CardDeck(range(0,51) /*array (front images)*/, "backImage" /*back image*/);

/*foreach ($deck as $card)
    echo($card."<br>");

$deck->shuffleDeck(1000); // shuffle n times

for ($i = 0; $i < 52; $i++)
    echo($deck[$i]."<br>");
*/
?>