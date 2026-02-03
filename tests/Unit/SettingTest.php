<?php

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Setting::query()->delete();
});

describe('Setting model', function () {
    test('can set and get a boolean setting', function () {
        Setting::set('test_setting', true, 'boolean');

        expect(Setting::get('test_setting'))->toBeTrue();
    });

    test('can set and get a string setting', function () {
        Setting::set('test_string', 'hello world', 'string');

        expect(Setting::get('test_string'))->toBe('hello world');
    });

    test('can set and get an integer setting', function () {
        Setting::set('test_integer', 42, 'integer');

        expect(Setting::get('test_integer'))->toBe(42);
    });

    test('can set and get a float setting', function () {
        Setting::set('test_float', 3.14, 'float');

        expect(Setting::get('test_float'))->toBe(3.14);
    });

    test('can set and get an array setting', function () {
        $array = ['foo' => 'bar', 'baz' => 'qux'];
        Setting::set('test_array', $array, 'array');

        expect(Setting::get('test_array'))->toBe($array);
    });

    test('boolean false is stored and retrieved correctly', function () {
        Setting::set('test_bool_false', false, 'boolean');

        expect(Setting::get('test_bool_false'))->toBeFalse();
    });

    test('boolean true is stored as 1 in database', function () {
        Setting::set('test_bool', true, 'boolean');

        $setting = Setting::where('key', 'test_bool')->first();
        expect($setting->value)->toBe('1')
            ->and($setting->type)->toBe('boolean');
    });

    test('boolean false is stored as 0 in database', function () {
        Setting::set('test_bool', false, 'boolean');

        $setting = Setting::where('key', 'test_bool')->first();
        expect($setting->value)->toBe('0')
            ->and($setting->type)->toBe('boolean');
    });

    test('updateOrCreate replaces existing setting', function () {
        Setting::set('test_setting', true, 'boolean');
        Setting::set('test_setting', false, 'boolean');

        expect(Setting::get('test_setting'))->toBeFalse()
            ->and(Setting::where('key', 'test_setting')->count())->toBe(1);
    });

    test('returns default value when setting does not exist', function () {
        expect(Setting::get('nonexistent', 'default'))->toBe('default');
    });

    test('returns null when setting does not exist and no default provided', function () {
        expect(Setting::get('nonexistent'))->toBeNull();
    });

    test('array is stored as json', function () {
        Setting::set('test_array', ['a' => 1], 'json');

        $setting = Setting::where('key', 'test_array')->first();
        expect($setting->value)->toBe('{"a":1}');
    });
});
