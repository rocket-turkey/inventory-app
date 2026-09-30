<?php
declare(strict_types=1);

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

final class InventoryController
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly StockMovementRepository $movements,
        private readonly InventoryService $inventory,
        private readonly InputValidator $validator,
        private readonly CsrfTokenManagerInterface $csrf,
        private readonly Environment $templates
    ) {}

    public function index(Request $request): Response
    {
        $editing = null;
        if ($request->query->has('edit')) {
            $id = filter_var($request->query->get('edit'), FILTER_VALIDATE_INT);
            if ($id && $id > 0) {
                $editing = $this->products->find($id);
            }
        }

        $html = $this->templates->render('index.html.twig', [
            'products' => $this->products->all(),
            'movements' => $this->movements->recent(),
            'editing' => $editing,
            'csrf' => $this->csrf->getToken('inventory')->getValue(),
            'flash' => $_SESSION['flash'] ?? null,
            'error' => $_SESSION['error'] ?? null,
        ]);
        unset($_SESSION['flash'], $_SESSION['error']);
        return new Response($html);
    }

    public function saveProduct(Request $request): Response
    {
        return $this->handleWrite($request, function () use ($request): string {
            [$sku, $name, $price, $reorder] = $this->validator->product($request->request->all());
            $id = filter_var($request->request->get('id'), FILTER_VALIDATE_INT);
            if ($id && $id > 0) {
                $this->products->update($id, $sku, $name, $price, $reorder);
            } else {
                $this->products->create($sku, $name, $price, $reorder);
            }
            return 'Product saved.';
        });
    }

    public function adjustStock(Request $request): Response
    {
        return $this->handleWrite($request, function () use ($request): string {
            [$id, $change, $note] = $this->validator->stock($request->request->all());
            $this->inventory->adjustStock($id, $change, $note);
            return 'Stock updated.';
        });
    }

    private function handleWrite(Request $request, callable $action): Response
    {
        if (!$this->csrf->isTokenValid(new CsrfToken('inventory', (string)$request->request->get('csrf', '')))) {
            return new Response('Invalid request token. Refresh the page and try again.', 403);
        }
        try {
            $_SESSION['flash'] = $action();
        } catch (PDOException $error) {
            $_SESSION['error'] = $error->getCode() === '23000'
                ? 'That SKU already exists.' : 'Database error. Please try again.';
        } catch (InvalidArgumentException $error) {
            $_SESSION['error'] = $error->getMessage();
        }
        return new RedirectResponse('/');
    }
}
