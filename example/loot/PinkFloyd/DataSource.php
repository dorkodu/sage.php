<?php

namespace PinkFloyd;

class DataSource
{
    private static const FILE = 'pseudo.db';
    private static $data = [
      'bandName' => "Pink Floyd",
      'about' => "Pink Floyd are an English rock band formed in London in 1965. Gaining an early following as one of the first British psychedelic groups, they were distinguished for their extended compositions, sonic experimentation, philosophical lyrics and elaborate live shows. They became a leading band of the progressive rock genre, cited by some as the greatest rock band of all time.",
      'members' => [
        1 => new Member('Syd Barrett', 'Roger Keith "Syd" Barrett (6 January 1946 – 7 July 2006) was an English singer, songwriter, and musician who co-founded the rock band Pink Floyd in 1965. Barrett was their original frontman and primary songwriter, becoming known for his whimsical style of psychedelia, English-accented singing, and stream-of-consciousness writing style. As a guitarist, he was influential for his free-form playing and for employing dissonance, distortion, echo, feedback, and other studio effects.'),
        2 => new Member('Roger Waters', 'George Roger Waters (born 6 September 1943) is an English musician, singer-songwriter and composer. In 1965, he co-founded the progressive rock band Pink Floyd. Waters initially served solely as the bassist, but following the departure of singer-songwriter Syd Barrett in 1968, he also became their lyricist, co-lead vocalist, conceptual leader and occasional rhythm guitarist until 1983.'),
        3 => new Member('David Gilmour', 'David Jon Gilmour CBE (born 6 March 1946) is an English guitarist, singer, songwriter and member of rock band Pink Floyd. He joined as guitarist and co-lead vocalist in 1967, shortly before the departure of founder member Syd Barrett. Following the departure of Roger Waters in 1985, Pink Floyd continued under Gilmour\'s leadership and released three more studio albums.'),
        4 => new Member('Nick Mason', 'Nicholas Berkeley Mason, CBE (born 27 January 1944) is an English drummer and co-founder of the progressive rock band Pink Floyd. He is the only member to feature on every Pink Floyd album, and the only constant member since its formation in 1965. He co-wrote many Pink Floyd compositions such as "Echoes", "Time", "Careful with That Axe, Eugene" and "One of These Days". In 2018, he formed a new band, Nick Mason\'s Saucerful of Secrets, to perform music from Pink Floyd\'s early years.'),
        5 => new Member('Rick Wright', 'Richard William Wright (28 July 1943 – 15 September 2008) was an English musician who was a co-founder of the progressive rock band Pink Floyd. He played keyboards and sang, appearing on almost every Pink Floyd album and performing on all their tours.'),
      ],
      'albums' => [
        1 => new Album('The Piper at the Gates of Dawn', 1967),
        2 => new Album('A Saucerful of Secrets', 1968),
        3 => new Album('MORE', 1969),
        4 => new Album('Ummagumma', 1969),
        5 => new Album('Atom Heart Mother', 1970),
        6 => new Album('Meddle', 1971),
        7 => new Album('Obscured By Clouds', 1972),
        8 => new Album('The Dark Side of the Moon', 1973),
        9 => new Album('Wish You Were Here', 1975),
        10 => new Album('Animals', 1976),
        11 => new Album('The Wall', 1979),
        12 => new Album('The Final Cut', 1981),
        13 => new Album('Momentary Lapse of Reason', 1987),
        14 => new Album('The Division Bell', 1994),
        15 => new Album('The Endless River', 2014),
      ],
      'reviews' => [
        1 => new Review(5, "Pink Floyd is the best band, ever.", 1657017118),
        2 => new Review(3, "i kinda like guitar solos, but songs are too long that i can't stand :(", 1657056100),
        3 => new Review(4, "besides the 'daddy died' albums, i think they are awesome!", 1654017115),
        4 => new Review(1, "all they did was taking drugs.", 1657000000),
      ],
      'songs' => [
        1 => new Song("", , ),

      ]
    ];

    public static function memberById(int $id)
    {
        return self::$data['members'][$id];
    }

    public static function memberByName(string $name)
    {
        foreach (self::$data['members'] as $member) {
            if ($name === $member->name) {
                return $member;
            }
        }
    }

    public static function members()
    {
        return self::$data['members'];
    }

    public static function albumById(int $id)
    {
        return self::$data['albums'][$id];
    }

    public static function albumByTitle(string $title)
    {
        foreach (self::$data['albums'] as $album) {
            if ($title === $album->title) {
                return $album;
            }
        }
    }

    public static function albums()
    {
        return self::$data['albums'];
    }

    public static function reviewById(int $id)
    {
        return self::$data['reviews'][$id];
    }

    public static function reviewByRating(int $rating)
    {
        foreach (self::$data['reviews'] as $review) {
            if ($rating === $review->rating) {
                return $review;
            }
        }
    }

    public static function reviews()
    {
        return self::$data['reviews'];
    }

    public static function addReview(int $rating, string $comment)
    {
        $id = self::generateId(self::$data['reviews']);
        $review = new Review($rating, $comment, time());

        self::$data['reviews'][$id] = $review;
    }
    
    public static function writeToFile()
    {
        $serialized = serialize(self::$data);
        file_put_contents(self::FILE, $serialized);
    }

    public static function readFromFile()
    {
        $raw = file_get_contents(self::FILE);
        self::$data = unserialize($raw);
    }

    private static function generateId($collection)
    {
        return count($collection) + 1;
    }
}
