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
	public function __construct($data,$description,$rating,$comments) {
		$this->data = $data;
		$this->description = $description;
		$this->rating = $rating;
		$this->comments = $comments;
	}
	
	/**
	 * Get all tools from DB
	 */
	public static function getAll() {
		// Construct tools query
		$sql_tools = 	'SELECT * FROM users INNER JOIN tools ON users.id=tools.user_id ORDER BY timestamp DESC';
		$sql_descriptions = 'SELECT users.*, d.description, d.tool_id
							 FROM users INNER JOIN descriptions as d
							 ON users.id=d.user_id WHERE d.timestamp=(
							 	SELECT MAX(timestamp) FROM descriptions WHERE tool_id=d.tool_id
							 )';
		$sql_ratings = 'SELECT DISTINCT ratings.user_id, tool_id, AVG(rating) as average, COUNT(rating) as numratings FROM tools INNER JOIN ratings ON tools.id=ratings.tool_id';
		$sql_comments = 'SELECT comments.*, firstname, lastname FROM comments INNER JOIN users on comments.user_id=users.id';
		
		// Execute queries
		$results_tools = parent::exec($sql_tools);
		$results_descriptions = parent::exec($sql_descriptions);
		$results_ratings = parent::exec($sql_ratings);
		$results_comments = parent::exec($sql_comments);
		
		// If there are no tools, STOP HERE
		if($results_tools == null) {
			return array();
		}
		
		// Loop over results, storing each as a Tool object into an array
		$tools = array();
		foreach($results_tools as $tool) {
			// Description
			$description = null;
			foreach($results_descriptions as $d) {
				if($d['tool_id'] == $tool['id']) {
					$description = $d['description'];
					break;
				}
			}
			
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
			$o = new Tool($tool,$description,$avg_rating,$comments);
			$tools[] = $o;
		}
		
		return $tools;
	}
	
	public function __toString() {
		$comment_count = count($this->comments);
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
		$toggle = isMobile() ? '' : 'data-toggle="collapse"';
		$tag = isMobile() ? 'span' : 'a';
		$url_tool = "./?p=tool_view&id={$this->id}";
		$onclick = isMobile() ? "onclick=\"window.location = './?p=tool_view&id={$this->id}';\"" : '';
		
		$summary = "
		<div class=\"tool\" data-tool-id=\"{$this->id}\" {$onclick}>
			<div class=\"row-fluid\">
				<div class=\"span12 tool-meta\">
					<div class=\"row\">
						<div class=\"span11\">
							<h3>{$this->name}<small class=\" hidden-phone pull-right\">{$this->firstname} {$this->lastname}</small></h3>
							<a href=\"http://{$this->url}\">{$this->url}</a>
							<p class=\"hidden-phone\">{$this->description}</p>
						</div>
						<div class=\"span1 tool-nav visible-phone\">
							<a href=\"{$url_tool}\"><i class=\"icon-chevron-right\"></i></a>
						</div>
					</div>
				</div>
			</div>
			<div class=\"feedback\">
				<div class=\"accordion\" id=\"comments-{$this->id}\">
					<div class=\"accordion-group\">
						<div class=\"accordion-heading row\">
							<div class=\"span3\">
								<{$tag} class=\"accordion-toggle pull-left comment-count\" {$toggle} data-parent=\"#comments-{$this->id}\" href=\"#user-comments-{$this->id}\">{$comment_count}</{$tag}>
								<a class=\"rating pull-right\">
									<i class=\"icon-star-empty\"></i>
									<i class=\"icon-star-empty\"></i>
									<i class=\"icon-star-empty\"></i>
									<i class=\"icon-star-empty\"></i>
									<i class=\"icon-star-empty\"></i>
								</a>
							</div>
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