package main

import (
	"log"
	"victorpimserviitk/campaigns"

	"github.com/gin-gonic/gin"
	"gorm.io/driver/sqlite"
	"gorm.io/gorm"
)

func main() {
	// Conexión a base de datos (usa SQLite de prueba)
	db, err := gorm.Open(sqlite.Open("test.db"), &gorm.Config{})
	if err != nil {
		log.Fatal("Error conectando a la base de datos:", err)
	}

	// Migrar modelo (crea tabla si no existe)
	if err := db.AutoMigrate(&campaigns.Campaign{}); err != nil {
		log.Fatal("Error en migración:", err)
	}

	// Inyectar dependencias
	repo := campaigns.NewRepository(db)
	service := campaigns.NewService(repo)
	handler := campaigns.NewHandler(service)

	// Iniciar servidor Gin
	r := gin.Default()
	r.PUT("/campaigns/update", handler.UpdateCampaign)
	log.Println("Servidor corriendo en http://localhost:8080")
	r.Run(":8080")
}
