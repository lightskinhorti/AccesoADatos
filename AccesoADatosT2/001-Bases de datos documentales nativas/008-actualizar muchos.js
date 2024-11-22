db.clientes.updateMany(
		{nombre:"Juan"},
		{ 
			$set: { nombre: "pablo" } 
		}
	
)
