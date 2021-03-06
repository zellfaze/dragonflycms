<?php
/*
	Dragonfly™ CMS, Copyright © since 2016
	http://dragonflycms.org

	Dragonfly CMS is released under the terms and conditions
	of the GNU GPL version 2 or any later version
*/

namespace Dragonfly\Modules\News;

class Comment extends \Dragonfly\Comments\Comment
{
	protected
		$story;

	function __construct(Story $story, array $data)
	{
		$this->story = $story;
		parent::__construct($data);
	}

	public static function factory(Story $story, $data)
	{
		$K = \Dragonfly::getKernel();
		if (!is_array($data)) {
			$tid = (int)$data;
			$data = $K->SQL->uFetchAssoc("SELECT
				tid id,
				pid parent_id,
				date,
				remote_ip,
				comment body,
				score,
				user_id
			FROM {$K->SQL->TBL->comments}
			WHERE sid = {$story->id} AND tid = {$tid}");
			if (!$data) {
				throw new \Exception('Survey comment not found', 404);
			}
		}
		$comment = new static($story, $data);
		if ($story->allow_reply) {
			$comment->reply_uri = \URL::index("&sid={$story->id}&reply={$comment->id}");
		}
		return $comment;
	}

	function __get($k)
	{
		if ('comments' === $k) {
			if (!is_array($this->comments)) {
				$this->comments = array();
				$ID = \Dragonfly::getKernel()->IDENTITY;
				if ('flat' !== $ID->umode) {
					$SQL = \Dragonfly::getKernel()->SQL;
					$order = 'date ASC';
					if (1 == $ID->uorder) {
						$order = 'date DESC';
					} else if (2 == $ID->uorder) {
						$order = 'score DESC, date ASC';
					}
					$result = $SQL->query("SELECT
						tid id,
						pid parent_id,
						date,
						remote_ip,
						comment body,
						score,
						user_id
					FROM {$SQL->TBL->comments}
					WHERE sid = {$this->story->id} AND pid = {$this->id}
					ORDER BY {$order}");
					while ($row = $result->fetch_assoc()) {
						$this->comments[] = self::factory($this->story, $row);
					}
				}
			}
			return $this->comments;
		}
	}

}
