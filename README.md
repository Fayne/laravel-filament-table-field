<p align="center"><img src="https://res.cloudinary.com/fayne/image/upload/v1787643269/fayne_logo.png" width="300"></p>

# Laravel Filament Table Field

简体中文 | [English](docs/README.en.md)

结构化表格字段，适用于 **PHP 8.4+、Laravel 13、Filament 5**。

## 功能与边界

独立表格编辑器，提供维度限制、结构归一化、合并单元格及单元格内容校验，不依赖 CMS 或 Page Builder。

## 安装

先在宿主 Composer 配置本私有包及所需 `fayne/*` 依赖的软件源，再执行：

```bash
composer require fayne/laravel-filament-table-field
```

Laravel 自动发现服务提供者。仅提供字段的包无需注册 Panel 插件；业务包按下面的示例注册。依赖版本以 [composer.json](composer.json) 为准，不要复制开发宿主的私有仓库凭据。

## 使用与配置

```php
use Fayne\TableField\Forms\Components\TableField;

TableField::make('specifications')->tableOptions([
    'minRows' => 1, 'maxRows' => 20,
    'minColumns' => 1, 'maxColumns' => 8,
]);
```

保存的是包含版本、行列数、单元格文档、合并锚点、样式及安全文本链接的结构化文档。服务端在渲染前归一化维度，保存前校验合并布局和内容节点。模型对应 JSON 字段应配置 `array` cast；这不是电子表格公式计算引擎。

## 测试与上线

本包已提供独立 Testbench 启动测试（`composer test`）；在宿主验证字段渲染、输入、保存后回显、无效值拒绝和权限边界。不要将文档检查视为运行时验收。

## 合作与贡献

通过 Issue 描述需求或缺陷，提供复现步骤、版本和预期行为；提交合并请求时补充相关验证，并说明尚未验证的环境。请勿提交密钥或业务敏感数据。商业合作及授权请联系 [Fayne Guo](mailto:fayne.guo@gmail.com)。

## 许可证

采用[专有许可证](LICENSE.md)。未经授权不得复制、分发或使用；第三方依赖仍遵循各自许可证。

## 开发与安全

[开发与 CI 约定](CONTRIBUTING.md) · [安全报告](SECURITY.md)。运行 `composer check` 检查语法和格式；有独立套件时同时运行测试。包不提交 `composer.lock`。
