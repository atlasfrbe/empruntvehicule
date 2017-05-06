<?php

class Fields {

	private $field;
	private $type;
	private $collation;
	private $null;
	private $key;
	private $default;
	private $extra;
	private $privileges;
	private $comment;
	private $link; // instance de la classe Links 
	
	public function __construct($donnees, $link) {
		foreach ($donnees as $cle=>$valeur) {
			$method = 'set'.ucfirst($cle);
			if (method_exists($this, $method)) {
				$this->$method($valeur);
			}
		}
		$this->link = $link;
	}
	
    public function getField() {
        return $this->field;
    }

    public function getType() {
        return $this->type;
    }

    public function getCollation() {
        return $this->collation;
    }

    public function getNull() {
        return $this->null;
    }

    public function getKey() {
        return $this->key;
    }

    public function getDefault() {
        return $this->default;
    }

    public function getExtra() {
        return $this->extra;
    }

    public function getPrivileges() {
        return $this->privileges;
    }

    public function getComment() {
        return $this->comment;
    }
	
	public function getLink() {
		return $this->link;
	}

    public function setField($info) {
        $this->field = $info;
    }

    public function setType($info) {
		if (strpos($info, 'int')!==false) {
			$this->type='I';
		}
		elseif (substr($info, 0, 3) == 'dec' or $info == 'float') {
			$this->type='F';
		}
		elseif (substr($info, 0, 4) == 'date' or $info == 'timestamp') {
			$this->type='D';
		}
		elseif ($info == 'text') {
			$this->type = 'T';
		}
		elseif (substr($info, 0, 7) == 'varchar') {
			$this->type = 
			substr($info, 8, 
				strpos($info, ')') -
				strpos($info, '(') -1);
		}
		elseif (substr($info, 0, 4) == 'char') {
			$this->type = 
			substr($info, 5, 
				strpos($info, ')') -
				strpos($info, '(') -1);
		}
		else {
			$this->type = $info;
		}
    }

    public function setCollation($info) {
        $this->collation = $info;
    }

    public function setNull($info) {
        $this->null = $info;
    }

    public function setKey($info) {
        $this->key = $info;
    }

    public function setDefault($info) {
		if ($info == 'CURRENT_TIMESTAMP') {
			$this->default = date('Y-m-d H:i:s');
		} else $this->default = $info;
    }

    public function setExtra($info) {
        $this->extra = $info;
    }

    public function setPrivileges($info) {
        $this->privileges = $info;
    }

    public function setComment($info) {
        $this->comment = $info;
    }
	
	public function getAlign() {
		if ($this->getLink() != null) return 'LEFT'; // A modifier car l'alignement doit correspondre à l'alignement du champ lié...
		switch ($this->type) {
			case 'I' : 
			case 'F' : return 'RIGHT';
			case 'D' : return 'CENTER';
			default : return 'LEFT';
		}
	}

	public function getInput($info, $suppl) {
		switch ($this->type) {
			case 'I' : 
			case 'F' : return '<INPUT type="text" name="'.$this->getField().'" size="12" maxlength="12" value="'.$info.'" style = "text-align: right;" '.$suppl.'  onKeypress="if((event.keyCode < 45 || event.keyCode > 57) && event.keyCode != 8 && event.keyCode != 0) event.returnValue = false; if((event.which < 45 || event.which > 57) && event.which != 8 && event.which != 0 ) return false;" onPaste="return false" />';
			case 'T' : return '<TEXTAREA name="'.$this->getField().'" '.$suppl.'">'.$info.'</TEXTAREA>';
			default : return '<INPUT type="text" name="'.$this->getField().'" size="'.$this->getType().'" maxlength="'.$this->getType().'" value="'.$info.'" '.$suppl.' />';
		}
		return $tmp;
	}

	public function getPDOParam() {
		switch ($this->type) {
			case 'I' : return PDO::PARAM_INT;
			default : return PDO::PARAM_STR;
		}
	}
}