<?php
class Tool extends DataModel {
	public static $plural = 'tools';
	
	public static function find($orderBy=null,$fields=null) {
		return parent::find('Tool',$orderBy,$fields);
	}
	
	/**
	 * Constructs a Tool object from DB associatve array
	 * @param Array $assoc
	 */
	public function __construct($data,$rating,$comments) {
		$this->data = $data;
		$this->rating = $rating;
		$this->comments = $comments;
	}
	
	/**
	 * Get all tools from DB
	 */
	public static function getAll() {
		// Construct tools query
		$sql_tools = 'SELECT DISTINCT tools.id, tools.name, tools.url, descriptions.description, users.firstname, users.lastname FROM users, tools JOIN descriptions ON tools.id=descriptions.tool_id WHERE descriptions.tool_id=users.id AND descriptions.timestamp=(SELECT MAX(timestamp) FROM descriptions WHERE descriptions.tool_id=tools.id)';
		$sql_ratings = 'SELECT DISTINCT ratings.user_id, tool_id, AVG(rating) as average, COUNT(rating) as numratings FROM tools INNER JOIN ratings ON tools.id=ratings.tool_id';
		$sql_comments = 'SELECT comments.*, firstname, lastname FROM comments INNER JOIN users on comments.user_id=users.id';
		
		// Execute queries
		$results_tools = parent::exec($sql_tools);
		$results_ratings = parent::exec($sql_ratings);
		$results_comments = parent::exec($sql_comments);
		
		// If there are no tools, STOP HERE
		if($results_tools == null) {
			return array();
		}
		
		// Loop over results, storing each as a Tool object into an array
		$tools = array();
		foreach($results_tools as $tool) {
			// Ratings
			$avg_rating = null;
			foreach($results_ratings as $rating) {
				if($rating['tool_id'] == $tool['id']) {
					$avg_rating = $rating['average'];
					break;
				}
			}
			
			// Comments
			$comments = array();
			foreach($results_comments as $comment) {
				if($comment['tool_id'] == $tool['id']) {
					$comments[] = $comment;
				}
			}
			$o = new Tool($tool,$avg_rating,$comments);
			$tools[] = $o;
		}
		
		return $tools;
	}
	
	public function __toString() {
		$comment_count = count($this->comments).' comment'.(count($this->comments) == 1 ? '' : 's');
		$comments = '';
		if(count($this->comments) > 0) {
			foreach($this->comments as $c)
			$comments .= "
				<div class=\"comment\">
					<h4>{$c['firstname']} {$c['lastname']}</h4>
					<h5>{$c['timestamp']}</h5>
					<p>{$c['comment']}</p>
				</div>
			";
		}
		
		$comment_form = "
		<form action=\"./?action=add_comment\" method=\"post\">
			<input type=\"hidden\" name=\"tool_id\" value=\"{$this->id}\"/>
			<textarea name=\"comment\" placeholder=\"Leave a comment...\" rows=\"1\"></textarea>
			<input class=\"btn\" type=\"submit\" value=\"Submit\" />
		</form>
		";
		
		$summary = "
		<div class=\"tool\" data-tool-id=\"{$this->id}\">
			<div class=\"row-fluid\">
				<div class=\"span10 tool-meta\">
					<h3>{$this->name}<small class=\"pull-right\">added by {$this->firstname} {$this->lastname}</small></h3>
					<a href=\"http://{$this->url}\">{$this->url}</a>
					<p>{$this->description}</p>
				</div>
			</div>
			<div class=\"feedback\">
				<div class=\"accordion\" id=\"comments-{$this->id}\">
					<div class=\"accordion-group\">
						<div class=\"accordion-heading row\">
							<a class=\"accordion-toggle span2\" data-toggle=\"collapse\" data-parent=\"#comments-{$this->id}\" href=\"#user-comments-{$this->id}\">{$comment_count}</a>
							<a class=\"span2\"><i class=\"icon-star\"></i></a>
						</div>
						<div id=\"user-comments-{$this->id}\" class=\"accordion-body collapse\">
							<div class=\"accordion-inner\">
								{$comments}
								{$comment_form}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>";
		return $summary;
	}
}