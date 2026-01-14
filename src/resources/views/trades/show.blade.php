@extends('layouts.app')

@section('title', '取引チャット')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/trades.css') }}">
<link rel="stylesheet" href="{{ asset('css/rating.css') }}">
@endpush

@section('content')
<div class="trade-chat-container">
    @php
    $isBuyer = $purchase->user_id === Auth::id();
    $partner = $isBuyer ? $purchase->item->user : $purchase->user;
    @endphp

    <!-- サイドバー（その他の取引一覧） -->
    <aside class="trade-sidebar">
        <h3 class="trade-sidebar__title">その他の取引</h3>
        @if($otherTrades->count() > 0)
        <div class="trade-sidebar__list">
            @foreach($otherTrades as $trade)
            <a href="{{ route('trades.show', $trade->id) }}" class="trade-sidebar__item">
                <span class="trade-sidebar__name">{{ Str::limit($trade->item->name, 20) }}</span>
            </a>
            @endforeach
        </div>
        @else
        <p class="trade-sidebar__empty">他の取引はありません</p>
        @endif
    </aside>

    <!-- メインエリア -->
    <div class="trade-main">
        <!-- ヘッダー（タイトル + 取引完了ボタン） -->
        <div class="trade-header">
            <div class="trade-header__user">
                <div class="trade-header__avatar">
                    @if($partner->profile && $partner->profile->profile_image)
                    <img src="{{ asset('storage/' . $partner->profile->profile_image) }}" alt="{{ $partner->name }}">
                    @endif
                </div>
                <h2 class="trade-header__title">「{{ $partner->name }}」さんとの取引画面</h2>
            </div>
            @if($isBuyer && !$purchase->is_completed)
            <form action="{{ route('trades.complete', $purchase->id) }}" method="POST" id="complete-form">
                @csrf
                <button type="submit" class="trade-complete__button" id="complete-button">
                    取引を完了する
                </button>
            </form>
            @endif
        </div>

        <!-- 商品情報エリア -->
        <div class="trade-product">
            <div class="trade-product__image">
                @if($purchase->item->image_url)
                @if(str_starts_with($purchase->item->image_url, 'http'))
                <img src="{{ $purchase->item->image_url }}" alt="{{ $purchase->item->name }}">
                @else
                <img src="{{ asset('storage/' . $purchase->item->image_url) }}" alt="{{ $purchase->item->name }}">
                @endif
                @endif
            </div>
            <div class="trade-product__info">
                <h3 class="trade-product__name">{{ $purchase->item->name }}</h3>
                <p class="trade-product__price">¥{{ number_format($purchase->item->price) }}</p>
            </div>
        </div>

        <!-- チャットエリア -->
        <div class="trade-chat">
            <!-- 相手ユーザー情報 -->
            <div class="trade-user">
                <div class="trade-user__avatar">
                    @if($partner->profile && $partner->profile->profile_image)
                    <img src="{{ asset('storage/' . $partner->profile->profile_image) }}" alt="{{ $partner->name }}">
                    @endif
                </div>
                <span class="trade-user__name">{{ $partner->name }}</span>
            </div>

            <!-- メッセージ一覧 -->
            <div class="trade-messages" id="messages-container">
                @foreach($messages as $message)
                @php
                $isOwnMessage = $message->sender_id === Auth::id();
                @endphp
                <div class="trade-message {{ $isOwnMessage ? 'trade-message--own' : 'trade-message--partner' }}">
                    {{-- ヘッダー（日時 + ユーザー名 + アバター） --}}
                    <div class="trade-message__header">
                        <span class="trade-message__time">{{ $message->created_at->format('Y/m/d H:i') }}</span>
                        <span class="trade-message__sender">{{ $message->sender->name }}</span>
                        <div class="trade-message__avatar">
                            @if($message->sender->profile && $message->sender->profile->profile_image)
                            <img src="{{ asset('storage/' . $message->sender->profile->profile_image) }}" alt="{{ $message->sender->name }}">
                            @endif
                        </div>
                    </div>
                    {{-- メッセージ本文 --}}
                    <div class="trade-message__content">
                        <div class="trade-message__body">
                            <div class="trade-message__display" id="message-display-{{ $message->id }}">
                                <p class="trade-message__text">{{ $message->message }}</p>
                                @if($message->image_path)
                                <div class="trade-message__image">
                                    <img src="{{ asset('storage/' . $message->image_path) }}" alt="添付画像">
                                </div>
                                @endif
                            </div>
                            {{-- 編集フォーム（初期非表示） --}}
                            @if($isOwnMessage)
                            <div class="trade-message__edit-form" id="message-edit-{{ $message->id }}" style="display: none;">
                                <form action="{{ route('trades.messages.update', $message->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <textarea name="message" class="trade-message__edit-textarea" required>{{ $message->message }}</textarea>
                                    <div class="trade-message__edit-actions">
                                        <button type="submit" class="trade-message__edit-save">保存</button>
                                        <button type="button" class="trade-message__edit-cancel" onclick="cancelEdit({{ $message->id }})">キャンセル</button>
                                    </div>
                                </form>
                            </div>
                            @endif
                        </div>
                    </div>
                    {{-- 編集・削除ボタン --}}
                    @if($isOwnMessage)
                    <div class="trade-message__actions" id="message-actions-{{ $message->id }}">
                        <button class="trade-message__edit" onclick="editMessage({{ $message->id }})">編集</button>
                        <form action="{{ route('trades.messages.destroy', $message->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="trade-message__delete" onclick="return confirm('メッセージを削除しますか？')">削除</button>
                        </form>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- メッセージ入力フォーム -->
            @if(!$purchase->is_completed)
            <div class="trade-input">
                @if($errors->any())
                <div class="error-messages">
                    @foreach($errors->all() as $error)
                    <p class="error-message">{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form action="{{ route('trades.messages.store', $purchase->id) }}" method="POST" enctype="multipart/form-data" class="trade-input__form">
                    @csrf
                    <textarea
                        name="message"
                        class="trade-input__textarea"
                        placeholder="取引メッセージを記入してください"
                        required>{{ old('message', $oldMessage) }}</textarea>
                    <label for="image-upload" class="trade-input__image-btn">
                        画像を追加
                        <input type="file" id="image-upload" name="image" accept="image/jpeg,image/png" class="trade-input__image-input">
                    </label>
                    <button type="submit" class="trade-input__submit">
                        <img src="{{ asset('images/send-icon.jpg') }}" alt="送信">
                    </button>
                </form>
                <div class="trade-input__image-name" id="image-name"></div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- 評価モーダル -->
@if(session('show_rating_modal') || ($purchase->is_completed &&
(($isBuyer && !$purchase->buyer_evaluated) || (!$isBuyer && !$purchase->seller_evaluated))))
<div class="rating-modal" id="rating-modal">
    <div class="rating-modal__content">
        <h3 class="rating-modal__title">取引が完了しました。</h3>
        <p class="rating-modal__subtitle">今回の取引相手はどうでしたか？</p>

        <form action="{{ route('ratings.store', $purchase->id) }}" method="POST" class="rating-form">
            @csrf
            <div class="rating-stars">
                <input type="radio" name="rating" value="5" id="star5" required>
                <label for="star5">★</label>
                <input type="radio" name="rating" value="4" id="star4">
                <label for="star4">★</label>
                <input type="radio" name="rating" value="3" id="star3">
                <label for="star3">★</label>
                <input type="radio" name="rating" value="2" id="star2">
                <label for="star2">★</label>
                <input type="radio" name="rating" value="1" id="star1">
                <label for="star1">★</label>
            </div>

            <div class="rating-button-area">
                <button type="submit" class="rating-submit">送信する</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
    // 文字数カウント
    const textarea = document.querySelector('.trade-input__textarea');
    const charCount = document.getElementById('char-count');
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
        // 初期値設定
        charCount.textContent = textarea.value.length;
    }

    // 画像選択時のファイル名表示
    const imageInput = document.getElementById('image-upload');
    const imageName = document.getElementById('image-name');
    if (imageInput && imageName) {
        imageInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                imageName.textContent = '選択: ' + this.files[0].name;
            } else {
                imageName.textContent = '';
            }
        });
    }

    // メッセージエリアを最下部にスクロール
    const messagesContainer = document.getElementById('messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // 評価モーダル表示
    const ratingModal = document.getElementById('rating-modal');
    if (ratingModal) {
        ratingModal.style.display = 'flex';
    }

    // 取引完了ボタンのダブルクリック防止
    function confirmComplete(button) {
        if (confirm('取引を完了しますか？')) {
            button.disabled = true;
            button.textContent = '処理中...';
            return true;
        }
        return false;
    }

    // メッセージ編集モードを開始
    function editMessage(messageId) {
        document.getElementById('message-display-' + messageId).style.display = 'none';
        document.getElementById('message-actions-' + messageId).style.display = 'none';
        document.getElementById('message-edit-' + messageId).style.display = 'block';
    }

    // メッセージ編集をキャンセル
    function cancelEdit(messageId) {
        document.getElementById('message-edit-' + messageId).style.display = 'none';
        document.getElementById('message-display-' + messageId).style.display = 'block';
        document.getElementById('message-actions-' + messageId).style.display = 'flex';
    }

    // FN009: 入力情報保持機能
    const messageInput = document.querySelector('.trade-input__textarea');
    if (messageInput) {
        window.addEventListener('beforeunload', function() {
            const message = messageInput.value.trim();
            if (message && !window.isFormSubmitting) {
                const formData = new FormData();
                formData.append('message', message);
                formData.append('_token', '{{ csrf_token() }}');
                navigator.sendBeacon('{{ route("trades.save-input", $purchase->id) }}', formData);
            }
        });

        const tradeForm = document.querySelector('.trade-input__form');
        if (tradeForm) {
            tradeForm.addEventListener('submit', function() {
                window.isFormSubmitting = true;
            });
        }
    }

    // サイドバー商品名の文字サイズ自動調整
    function fitTextToContainer() {
        const items = document.querySelectorAll('.trade-sidebar__name');
        items.forEach(function(item) {
            const container = item.parentElement;
            const containerWidth = container.clientWidth - 30; // padding分を引く
            let fontSize = 14;
            item.style.fontSize = fontSize + 'px';

            while (item.scrollWidth > containerWidth && fontSize > 10) {
                fontSize--;
                item.style.fontSize = fontSize + 'px';
            }
        });
    }

    // ページ読み込み後に実行
    fitTextToContainer();
    window.addEventListener('resize', fitTextToContainer);
</script>
@endpush
@endsection