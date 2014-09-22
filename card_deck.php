<?php

class Card
{
    private $color;
    private $weight;
    //TODO: private $frontImage;
    
    public function __construct($color, $weight)
    {
        $this->color = $color;
        $this->weight = $weight;
    }

    public function getColor() { return $this->color; }
    public function getWeight() { return $this->weight; }

    public function __toString()
    {
        if (($this->weight >= 11) && ($this->weight <= 14))
        {
            return ("Card: " .
                $this->getConstantName("CardWeight", $this->weight) . " of " .
                $this->getConstantName("CardColor", $this->color));
        }
        else
        {
            return ("Card: " . $this->weight . " of " .
                $this->getConstantName("CardColor", $this->color));
        }
    }

    private function getConstantName($class, $value)
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

class CardDeck
{
    private $deck = [];

    public function __construct()
    {
        for ($i = 0; $i < 4; $i++)
            for ($j = 2; $j <= 14; $j++)
            {
                $card = new Card($i, $j);
                array_push($this->deck, $card);
               // echo($card . "\n");
            }
    }
    
    public function getCard($cardColor, $cardWeight)
    {
        foreach ($this->deck as $card)
        {
            if (($card->getColor() == $cardColor) &&
                ($card->getWeight() == $cardWeight))
            {
                return $card;
            }
        }

    }
}

//$card = new Card(CardColor::Clubs, CardWeight::King);
$deck = new CardDeck();
echo($deck->getCard(CardColor::Hearts, CardWeight::King) . "\n");
echo($deck->getCard(CardColor::Spades, CardWeight::Ace) . "\n");
echo($deck->getCard(CardColor::Diamonds, 2) . "\n");

?>