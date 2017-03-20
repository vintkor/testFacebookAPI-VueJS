@extends('layouts.app')

@section('content')
    <div id="vueapp">
        <div class="container">
            <div v-if="showButton" class="text-center animated fadeIn">
                <a v-on:click.prevent="setFacebookCookie" href="{{ $login_url }}" class="btn btn-lg btn-success">
                    Connect to my Facebook profile
                </a>
            </div>
            <div v-if="showUser" class="row animated fadeIn">
                <div class="col-md-12">
                    <h1 class="text-center text-capitalize page__title">@{{ userData.name }}</h1>
                </div>
            </div>
            <div class="row flex">
                <div v-for="post in userPosts" class="col-md-4 post animated fadeIn">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="post__meta">
                                <div class="post__date">@{{ post.created_time.date }}</div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <img :src="post.picture" class="post__image">
                            @{{ ("message" in post) ? post.message : post.description }}
                            <div class="clearfix"></div>
                            <div class="comments">
                                <div v-for="comment in post.comments" class="comments__post">
                                    <h5 class="comments__from pull-left">@{{ comment.from.name }}</h5>
                                    <div class="clearfix"></div>
                                    <div class="comments__text">@{{ comment.message }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer  text-center">
                            <span class="pull-left">
                                <i class="fa fa-thumbs-o-up" aria-hidden="true"></i>
                                @{{ ("likes" in post) ? post.likes.length : 0 }}
                            </span>
                            <span>
                                <i class="fa fa-comments-o" aria-hidden="true"></i>
                                @{{ ("comments" in post) ? post.comments.length : 0 }}
                            </span>
                            <span class="pull-right">
                                <i class="fa fa-share" aria-hidden="true"></i>
                                @{{ ("shares" in post) ? post.shares.count : 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="//connect.facebook.net/en_US/all.js"></script>
    <script src="https://unpkg.com/vue@2.2.4"></script>
    <script type="text/javascript">
        new Vue({
            el: '#app',
            data: {
                userPosts: {},
                userData: {},
                showButton: true,
                showUser: false
            },
            methods: {
                setFacebookCookie: function() {
                    window.fbAsyncInit = FB.init({
                        appId: '{{ env('FACEBOOK_APP_ID') }}',
                        cookie: true, // This is important, it's not enabled by default
                        version: 'v2.8'
                    });
                    this.connectToFacebook();
                },
                connectToFacebook: function() {
                    this.showButton = false;
                    var self = this;
                    FB.login(function(response) {
                        if (response.authResponse) {
                            $.getJSON("{{ route('callback') }}", function(data){
                                self.userData = data[0]['user_data'];
                                self.userPosts = data[0]['user_posts'];
                                self.showUser = true;
                            });
                        } else {
                            return false;
                        }
                    });
                    setTimeout(this.setFacebookCookie, 10000);
                }
            }
        });
    </script>
@endpush


@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
<style>
    .flex {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: stretch;
    }
    .post {
        margin-bottom: 30px;
    }
/*    .panel-default {
        display: flex;
        height: 100%;
        flex-wrap: wrap;
        align-content: space-between;
    }*/
    .panel-heading,
    .panel-body,
    .panel-footer {
        width: 100%;
        overflow: hidden;
    }
    .page__title {
        margin-bottom: 30px;
    }
    .post__meta {
        text-align: right;
    }
    .post__image {
        float: left;
        margin-right: 15px;
        margin-bottom: 15px;
        width: 130px;
        height: auto;
    }
    .comments {
        margin-left: -16px;
        width: calc(100% + 32px);
        margin-bottom: -16px;
    }
    .comments__post {
        background: #fbfbfb;
        border: 1px solid #d3e0e9;
        margin-top: -1px;
        padding: 0 15px;
    }
    .comments__from {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 0;
    }
    .comments__text {
        font-size: 13px;
    }
</style>
@endpush