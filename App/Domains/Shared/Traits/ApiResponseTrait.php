<?php

namespace App\Domains\Shared\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Trait para respuestas API consistentes
 * 
 * Elimina duplicación de código en controladores para respuestas JSON
 * Cumple con el principio DRY y proporciona consistencia en las respuestas
 */
trait ApiResponseTrait
{
    /**
     * Respuesta de éxito
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Respuesta de error
     *
     * @param string $message
     * @param mixed $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Error', $errors = null, int $statusCode = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Respuesta de validación fallida
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    protected function validationErrorResponse(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Respuesta de recurso no encontrado
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, null, Response::HTTP_NOT_FOUND);
    }

    /**
     * Respuesta de no autorizado
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, null, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Respuesta de prohibido
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, null, Response::HTTP_FORBIDDEN);
    }

    /**
     * Respuesta de conflicto
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function conflictResponse(string $message = 'Conflict', $errors = null): JsonResponse
    {
        return $this->errorResponse($message, $errors, Response::HTTP_CONFLICT);
    }

    /**
     * Respuesta de error interno del servidor
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse($message, null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Respuesta de búsqueda vacía
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function emptySearchResponse(string $message = 'No results found'): JsonResponse
    {
        return $this->successResponse([], $message);
    }

    /**
     * Respuesta de búsqueda con resultados
     *
     * @param mixed $results
     * @param string $message
     * @return JsonResponse
     */
    protected function searchResponse($results, string $message = 'Search completed'): JsonResponse
    {
        return $this->successResponse($results, $message);
    }

    /**
     * Respuesta de creación exitosa
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function createdResponse($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Respuesta de actualización exitosa
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function updatedResponse($data = null, string $message = 'Resource updated successfully'): JsonResponse
    {
        return $this->successResponse($data, $message);
    }

    /**
     * Respuesta de eliminación exitosa
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function deletedResponse(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return $this->successResponse(null, $message);
    }

    /**
     * Respuesta paginada
     *
     * @param mixed $paginatedData
     * @param string $message
     * @return JsonResponse
     */
    protected function paginatedResponse($paginatedData, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return $this->successResponse([
            'data' => $paginatedData->items(),
            'pagination' => [
                'current_page' => $paginatedData->currentPage(),
                'last_page' => $paginatedData->lastPage(),
                'per_page' => $paginatedData->perPage(),
                'total' => $paginatedData->total(),
                'from' => $paginatedData->firstItem(),
                'to' => $paginatedData->lastItem(),
            ]
        ], $message);
    }
}
