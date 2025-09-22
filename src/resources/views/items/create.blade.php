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
            <div class="form-section image-section">
                <h3 class="section-title">商品画像</h3>
                <div class="image-upload-area" id="imageUploadArea">
                    <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;">

                    <div class="upload-placeholder" id="uploadPlaceholder" onclick="document.getElementById('imageInput').click();">
                        <div class="upload-text">画像を選択する</div>
                    </div>
                </div>
                <div class="image-preview" id="imagePreview" style="{{ old('existing_image') ? 'display: block;' : 'display: none;' }}">
                    <img id="previewImage" src="{{ old('existing_image') ? old('existing_image') : '' }}" alt="プレビュー">
                    <button type="button" class="remove-image" id="removeImage">削除</button>
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
                    <select name="condition_id" id="condition_id">
                        <option value="">選択してください</option>
                        @foreach($conditions as $condition)
                        <option value="{{ $condition->id }}" {{ old('condition_id') == $condition->id ? 'selected' : '' }}>
                            {{ $condition->name }}
                        </option>
                        @endforeach
                    </select>
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