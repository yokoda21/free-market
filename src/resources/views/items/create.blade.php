@extends('layouts.app')

@section('title', '商品出品 - coachtech フリマ')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/create.css') }}">
@endpush

@section('content')
<main class="create-main">
    <div class="create-container">
        <h1 class="create-title">商品の出品</h1>

        <form class="create-form" action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            <!-- 商品画像 -->
            <h3 class="section-title">商品画像</h3>
            <div class="form-section image-section">
                <div class="image-upload-container">
                    <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;">

                    <!-- アップロードエリア（画像選択前） -->
                    <div class="image-upload-area" id="uploadPlaceholder" onclick="document.getElementById('imageInput').click();" style="{{ old('existing_image') ? 'display: none;' : 'display: block;' }}">
                        <div class="upload-placeholder">
                            <div class="upload-text">画像を選択する</div>
                        </div>
                    </div>

                    <!-- 画像プレビューエリア（画像選択後） -->
                    <div class="image-preview" id="imagePreview" style="{{ old('existing_image') ? 'display: block;' : 'display: none;' }}">
                        <img id="previewImage" src="{{ old('existing_image') ? old('existing_image') : '' }}" alt="プレビュー">
                        <button type="button" class="remove-image" id="removeImage">削除</button>
                    </div>
                </div>
                @error('image')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- 商品の詳細 -->
            <div class="form-section details-section">
                <h3 class="section-title">商品の詳細</h3>

                <!-- カテゴリー -->
                <div class="form-group">
                    <label>カテゴリー</label>
                    <div class="category-grid">
                        @foreach($categories as $category)
                        <label class="category-item">
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }}>
                            <span class="category-label">{{ $category->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('category_ids')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>


                <!-- 商品の状態 -->
                <div class="form-group">
                    <label>商品の状態</label>

                    <!-- カスタムドロップダウン -->
                    <div class="custom-dropdown-condition @error('condition_id') error @enderror">
                        <input type="hidden" name="condition_id" id="condition_id_hidden" value="{{ old('condition_id', '') }}">
                        <div class="dropdown-selected-condition" id="dropdown_selected_condition">
                            <span class="selected-text-condition">選択してください</span>
                            <span class="dropdown-arrow-condition">▼</span>
                        </div>
                        <div class="dropdown-options-condition" id="dropdown_options_condition">
                            @foreach($conditions as $condition)
                            <div class="dropdown-option-condition" data-value="{{ $condition->id }}">
                                <span class="option-check-condition">✓</span>
                                <span class="option-text-condition">{{ $condition->name }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    @error('condition_id')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <!-- 商品名と説明 -->
            <div class="form-section name-description-section">
                <h3 class="section-title">商品名と説明</h3>

                <!-- 商品名 -->
                <div class="form-group">
                    <label>商品名</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input">
                    @error('name')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- ブランド名 -->
                <div class="form-group">
                    <label>ブランド名</label>
                    <input type="text" name="brand" value="{{ old('brand') }}" class="form-input">
                    @error('brand')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- 商品の説明 -->
                <div class="form-group">
                    <label>商品の説明</label>
                    <textarea name="description" rows="6" class="form-textarea">{{ old('description') }}</textarea>
                    @error('description')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- 販売価格 -->
                <div class="form-group">
                    <label>販売価格</label>
                    <div class="price-input-group">
                        <span class="price-prefix">¥</span>
                        <input type="number" name="price" value="{{ old('price') }}" class="form-input price-input">
                    </div>
                    @error('price')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- 出品ボタン -->
            <button type="submit" class="btn-submit">出品する</button>
        </form>
    </div>
</main>
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ========== 画像プレビュー機能 ==========
        const imageInput = document.getElementById('imageInput');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const imagePreview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');
        const removeImageBtn = document.getElementById('removeImage');

        // 画像選択時
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImage.src = event.target.result;
                    uploadPlaceholder.style.display = 'none';
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // 画像削除ボタン
        removeImageBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            imageInput.value = '';
            previewImage.src = '';
            uploadPlaceholder.style.display = 'block';
            imagePreview.style.display = 'none';
        });

        // ========== 商品の状態カスタムドロップダウン ==========
        const dropdown = document.querySelector('.custom-dropdown-condition');
        const selected = document.getElementById('dropdown_selected_condition');
        const optionsContainer = document.getElementById('dropdown_options_condition');
        const options = document.querySelectorAll('.dropdown-option-condition');
        const hiddenInput = document.getElementById('condition_id_hidden');
        const selectedText = document.querySelector('.selected-text-condition');

        // ドロップダウン開閉
        selected.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('active');
        });

        // オプション選択
        options.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                const text = this.querySelector('.option-text-condition').textContent;

                // 選択状態を更新
                options.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');

                // 表示を更新
                selectedText.textContent = text;
                hiddenInput.value = value;

                // ドロップダウンを閉じる
                dropdown.classList.remove('active');
            });
        });

        // 外側クリックで閉じる
        document.addEventListener('click', function() {
            dropdown.classList.remove('active');
        });

        // 既存の値を復元
        const currentValue = hiddenInput.value;
        if (currentValue) {
            const selectedOption = document.querySelector(`.dropdown-option-condition[data-value="${currentValue}"]`);
            if (selectedOption) {
                selectedOption.click();
            }
        }
    });
</script>
@endpush