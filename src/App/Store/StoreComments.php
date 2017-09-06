<?php

namespace App\Store;

use Container\DatabaseContainer;
use PDO;
use RuntimeException;

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
        $database = DatabaseContainer::getDatabase();

        $oGetCommentList = $database->prepare('SELECT * FROM `store_comments` WHERE `product_id`=:product_id ORDER BY `updated` DESC');
        $oGetCommentListSuccessful = $oGetCommentList->execute(array(
            ':product_id' => $product_id,
        ));
        if (!$oGetCommentListSuccessful) {
            throw new RuntimeException('Could not execute sql');
        } else {
            if (@$oGetCommentList->rowCount() == 0) {
                return array();
            } else {
                $comment_list = $oGetCommentList->fetchAll(PDO::FETCH_ASSOC);
                return $comment_list;
            }
        }
    }

    /**
     * @param int    $product_id
     * @param int    $user_id
     * @param string $comment
     * @param int    $stars
     *
     * @throws \Exception
     */
    public static function addReview($product_id, $user_id, $comment, $stars)
    {
        $database = DatabaseContainer::getDatabase();

        if ($stars > 5) {
            $stars = 5;
        }

        // Insert review
        $sql = $database->prepare('INSERT INTO `store_comments`(`product_id`,`user_id`,`rating`,`comment`,`created`,`updated`) VALUES (:product_id,:user_id,:rating,:comment,:time,:time)');
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
        $product->setRatingCount($rating);

        // Update stars
        $sql2 = $database->prepare('SELECT `rating` FROM `store_comments` WHERE `product_id`=:product_id');
        $sql2->execute(array(
            ':product_id' => $product_id,
        ));

        $ustars = 0;
        $count = 0;
        foreach ($sql2->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $ustars += $item['rating'];
            $count++;
        }
        $ustars += $stars;
        $count++;

        $average = round(($ustars / $count) * 2)/2;

        $product->setRatingCache($average);
    }

    /******************************************************************************/

    private $commentId;
    private $commentData;

    /**
     * StoreComments constructor.
     *
     * @param int $comment_id
     *
     * @throws \Exception
     */
    public function __construct($comment_id)
    {
        $this->commentId = $comment_id;

        $database = DatabaseContainer::getDatabase();

        $getData = $database->prepare('SELECT * FROM `store_comments` WHERE `id`=:comment_id LIMIT 1');
        $getData->bindValue(':comment_id', $this->commentId, PDO::PARAM_INT);
        $sqlSuccess = $getData->execute();

        if (!$sqlSuccess) {
            throw new \RuntimeException('Could not execute sql');
        } else {
            if ($getData->rowCount() > 0) {
                $data            = $getData->fetchAll(PDO::FETCH_ASSOC);
                $this->commentData = $data[0];
            } else {
                $this->commentData = null;
            }
        }
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        $value = $this->commentData[$key];

        return $value;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @throws \Exception
     *
     * @deprecated Function not working
     */
    public function setVar($key, $value)
    {
        $database = DatabaseContainer::getDatabase();

        $oUpdateTable = $database->prepare('UPDATE `store_comments` SET :key=:value WHERE `id`=:product_id');
        $bUpdateTableQuerySuccessful = $oUpdateTable->execute(array(
            ':key'        => $key,
            ':value'      => $value,
            ':product_id' => $this->commentId,
        ));
        if (!$bUpdateTableQuerySuccessful) {
            throw new RuntimeException('Could not execute sql');
        }
    }
}
