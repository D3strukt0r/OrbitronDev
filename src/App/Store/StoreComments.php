<?php

namespace App\Store;

use App\Core\DatabaseConnection;

class StoreComments
{
    /**
     * @param float $product_id
     *
     * @return array
     * @throws \Exception
     */
    public static function getCommentList($product_id)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetCommentList = $database->prepare('SELECT `id` FROM `store_comments` WHERE `product_id`=:product_id ORDER BY `updated` DESC');
        $oGetCommentListSuccessful = $oGetCommentList->execute(array(
            ':product_id' => $product_id,
        ));
        if (!$oGetCommentListSuccessful) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if (@$oGetCommentList->rowCount() == 0) {
                return array();
            } else {
                $comment_list = $oGetCommentList->fetchAll(\PDO::FETCH_ASSOC);
                return $comment_list;
            }
        }
    }

    /**
     * @param float  $product_id
     * @param float  $user_id
     * @param string $comment
     * @param int    $stars
     *
     * @throws \Exception
     */
    public static function addReview($product_id, $user_id, $comment, $stars)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $product_id = (float)$product_id;
        $comment = (string)$comment;
        $stars = (int)$stars;

        if ($stars > 5) {
            $stars = 5;
        }

        // Insert review
        $sql = $database->prepare('INSERT INTO `store_comments`(`product_id`,`user_id`,`rating`,`comment`,`created_at`,`updated_at`) VALUES (:product_id,:user_id,:rating,:comment,:time)');
        $sql->execute(array(
            ':product_id' => $product_id,
            ':user_id'    => $user_id,
            ':rating'     => $stars,
            ':comment'    => $comment,
            ':time'       => time(),
        ));

        $product = new StoreProduct($product_id);

        // Update rating count
        $rating = $product->getVar('rating_count');
        $rating = $rating + 1;
        $product->setVar('rating_count', $rating);

        // Update stars
        $sql2 = $database->prepare('SELECT `rating` FROM `store_comments` WHERE `product_id`=:product_id');
        $sql2->execute(array(
            ':product_id' => $product_id,
        ));

        $ustars = 0;
        $count = 0;
        foreach ($sql2->fetchAll(\PDO::FETCH_ASSOC) as $item) {
            $ustars += $rating['rating'];
            $count++;
        }
        $ustars += $stars;
        $count++;

        (float)$average = $ustars / $count;

        $product->setVar('rating_cache', $average);
    }

    /******************************************************************************/

    private $iCommentId;
    private $aCommentData;

    /**
     * StoreComments constructor.
     *
     * @param float $comment_id
     *
     * @throws \Exception
     */
    public function __construct($comment_id)
    {
        $this->iCommentId = $comment_id;

        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oGetCommentData = $database->prepare('SELECT * FROM `store_comments` WHERE `id`=:comment_id LIMIT 1');
        $bGetCommentDataSuccessful = $oGetCommentData->execute(array(
            ':comment_id' => $this->iCommentId,
        ));
        if (!$bGetCommentDataSuccessful) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            $aCommentData = $oGetCommentData->fetchAll();
            $this->aCommentData = $aCommentData[0];
        }
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        $value = $this->aCommentData[$key];
        return $value;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @throws \Exception
     */
    public function setVar($key, $value)
    {
        /** @var \PDO $database */
        $database = DatabaseConnection::$database;
        if (is_null($database)) {
            throw new \Exception('A database connection is required');
        }

        $oUpdateTable = $database->prepare('UPDATE `store_comments` SET :key=:value WHERE `id`=:product_id');
        $bUpdateTableQuerySuccessful = $oUpdateTable->execute(array(
            ':key'        => $key,
            ':value'      => $value,
            ':product_id' => $this->iCommentId,
        ));
        if (!$bUpdateTableQuerySuccessful) {
            throw new \RuntimeException('Could not execute sql');
        }
    }
}
