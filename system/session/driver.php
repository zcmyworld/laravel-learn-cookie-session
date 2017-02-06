<?php

namespace System\Session;

interface Driver {

	// 加载 session
	public function load($id);

	// 保存 session
	public function save($session);

	// 根据id删除 session
	public function delete($id);

	// 删除超时 session
	public function sweep($expiration);

}