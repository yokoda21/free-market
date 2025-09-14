<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>商品出品 - coachtech フリマ</title>

    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/create.css') }}">

</head>

<body>
    <div class="container">
        <!-- ヘッダー -->
        <header class="header">
            <div class="header-content">
                <a href="/" class="logo">
                    <img src="{{ asset('images/logo.svg') }}" alt="coachtech">
                </a>

                <div class="header-actions">
                    @auth
                    <a href="{{ route('items.create') }}" class="btn-sell">出品</a>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-logout">ログアウト</button>
                    </form>
                    <a href="/mypage" class="btn-mypage">マイページ</a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- メインコンテンツ -->
        <main class="main-content">
            <!-- フラッシュメッセージ -->
            @if (session('success'))
            <div class="flash-message success">
                {{ session('success') }}
            </div>
            @endif

            @if (session('error'))
            <div class="flash-message error">
                {{ session('error') }}
            </div>
            @endif

            <div class="create-container">
                <div class="create-content">
                    <!-- ページタイトル -->
                    <div class="page-header">
                        <h1>商品の出品</h1>
                        <p class="page-description">
                            商品情報を入力して出品してください
                        </p>
                    </div>

                    <!-- 出品フォーム -->
                    <form class="create-form" action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf

                        <!-- 商品画像アップロード -->
                        <div class="form-section">
                            <h3>商品画像</h3>
                            <div class="form-group">
                                <div class="image-upload-area" id="imageUploadArea">
                                    <input type="file" name="image" id="imageInput" accept="image/*">
                                    <!-- 保持された画像があれば非表示 -->
                                    <input type="hidden" name="existing_image" id="existingImagePath" value="{{ old('existing_image') }}">

                                    <div class="upload-placeholder" id="uploadPlaceholder" style="{{ old('existing_image') ? 'display: none;' : '' }}">
                                        <div class="upload-icon">📷</div>
                                        <p>画像をアップロード</p>
                                        <p class="upload-note">JPG, PNG, GIF (最大10MB)</p>
                                    </div>
                                    <div class="image-preview" id="imagePreview" style="{{ old('existing_image') ? 'display: block;' : 'display: none;' }}">
                                        <img id="previewImage" src="{{ old('existing_image') ? old('existing_image') : '' }}" alt="プレビュー">
                                        <button type="button" class="remove-image" id="removeImage">削除</button>
                                    </div>
                                </div>
                                @if($errors->has('image') && old('_token'))
                                <span class="error-message">{{ $errors->first('image') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- 商品の詳細 -->
                        <div class="form-section">
                            <h3>商品の詳細</h3>

                            <!-- カテゴリー -->
                            <div class="form-group">
                                <label for="category_ids" class="required">カテゴリー</label>
                                <div class="category-grid">
                                    @foreach($categories as $category)
                                    <label class="category-item">
                                        <input type="checkbox"
                                            name="category_ids[]"
                                            value="{{ $category->id }}"
                                            {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }}>
                                        <span class="category-label">{{ $category->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                @if($errors->has('category_ids') && old('_token'))
                                <span class="error-message">{{ $errors->first('category_ids') }}</span>
                                @endif
                            </div>

                            <!-- 商品の状態 -->
                            <div class="form-group">
                                <label for="condition_id" class="required">商品の状態</label>
                                <select name="condition_id" id="condition_id">
                                    <option value="">選択してください</option>
                                    @foreach($conditions as $condition)
                                    <option value="{{ $condition->id }}"
                                        {{ old('condition_id') == $condition->id ? 'selected' : '' }}>
                                        {{ $condition->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @if($errors->has('condition_id') && old('_token'))
                                <span class="error-message">{{ $errors->first('condition_id') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- 商品名と説明 -->
                        <div class="form-section">
                            <h3>商品名と説明</h3>

                            <!-- 商品名 -->
                            <div class="form-group">
                                <label for="name" class="required">商品名</label>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="{{ old('name') }}">
                                @if($errors->has('name') && old('_token'))
                                <span class="error-message">{{ $errors->first('name') }}</span>
                                @endif
                            </div>

                            <!-- 商品の説明 -->
                            <div class="form-group">
                                <label for="description" class="required">商品の説明</label>
                                <textarea
                                    name="description"
                                    id="description"
                                    rows="6">{{ old('description') }}</textarea>
                                <div class="char-count">
                                    <span id="descriptionCount">{{ old('description') ? strlen(old('description')) : 0 }}</span>/255文字
                                </div>
                                @if($errors->has('description') && old('_token'))
                                <span class="error-message">{{ $errors->first('description') }}</span>
                                @endif
                            </div>

                            <!-- ブランド名 -->
                            <div class="form-group">
                                <label for="brand">ブランド名</label>
                                <input
                                    type="text"
                                    name="brand"
                                    id="brand"
                                    value="{{ old('brand') }}">
                                @if($errors->has('brand') && old('_token'))
                                <span class="error-message">{{ $errors->first('brand') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- 販売価格 -->
                        <div class="form-section">
                            <h3>販売価格</h3>

                            <div class="form-group">
                                <label for="price" class="required">販売価格</label>
                                <div class="price-input-group">
                                    <span class="price-prefix">¥</span>
                                    <input
                                        type="number"
                                        name="price"
                                        id="price"
                                        value="{{ old('price') }}"
                                        min="0"
                                        max="9999999">
                                </div>
                                @if($errors->has('price') && old('_token'))
                                <span class="error-message">{{ $errors->first('price') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- 出品ボタン -->
                        <div class="form-actions">
                            <a href="/" class="btn-cancel">キャンセル</a>
                            <button type="submit" class="btn-submit">出品する</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // 画像アップロード機能
        const imageInput = document.getElementById('imageInput');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const imagePreview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');
        const removeImageBtn = document.getElementById('removeImage');
        const uploadArea = document.getElementById('imageUploadArea');
        const existingImagePath = document.getElementById('existingImagePath');

        // ファイル選択時の処理
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // ファイルサイズチェック (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert('ファイルサイズは10MB以下にしてください。');
                    imageInput.value = '';
                    return;
                }

                // 画像プレビュー表示
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    uploadPlaceholder.style.display = 'none';
                    imagePreview.style.display = 'block';
                    // 新しい画像を選択したので既存画像パスをクリア
                    existingImagePath.value = '';
                };
                reader.readAsDataURL(file);
            }
        });

        // 画像削除機能
        removeImageBtn.addEventListener('click', function() {
            imageInput.value = '';
            previewImage.src = '';
            uploadPlaceholder.style.display = 'block';
            imagePreview.style.display = 'none';
            existingImagePath.value = '';
        });

        // ドラッグ&ドロップ機能
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                imageInput.files = files;
                imageInput.dispatchEvent(new Event('change'));
            }
        });

        // 文字数カウント機能
        const descriptionTextarea = document.getElementById('description');
        const descriptionCount = document.getElementById('descriptionCount');

        function updateCharCount() {
            const length = descriptionTextarea.value.length;
            descriptionCount.textContent = length;

            if (length > 255) {
                descriptionCount.style.color = '#ff4444';
            } else {
                descriptionCount.style.color = '#666';
            }
        }

        descriptionTextarea.addEventListener('input', updateCharCount);
        updateCharCount(); // 初期表示

        // フラッシュメッセージの自動消去
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(message => {
            setTimeout(() => {
                message.style.display = 'none';
            }, 5000);
        });

        // 価格入力の数値のみ制限
        const priceInput = document.getElementById('price');
        priceInput.addEventListener('input', function(e) {
            let value = e.target.value;
            // 負の数を除去
            if (value < 0) {
                e.target.value = 0;
            }
        });

        // フォーム送信前の画像データ保存
        const createForm = document.querySelector('.create-form');
        createForm.addEventListener('submit', function(e) {
            // 現在プレビューされている画像がある場合、Base64データを保存
            if (previewImage.src && previewImage.src.startsWith('data:')) {
                existingImagePath.value = previewImage.src;
            }
        });
    </script>

</body>

</html>